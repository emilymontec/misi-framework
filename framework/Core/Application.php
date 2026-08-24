<?php

declare(strict_types=1);

namespace Misi\Core;

use Misi\Auth\Auth;
use Misi\Database\Database;
use Misi\Exceptions\AuthenticationException;
use Misi\Exceptions\AuthorizationException;
use Misi\Exceptions\HttpException;
use Misi\Exceptions\ValidationException;
use Misi\Http\JsonResponse;
use Misi\Http\Request;
use Misi\Http\Response;
use Misi\Logging\Logger;
use Misi\Routing\Router;
use Misi\Security\Csrf;
use Misi\Storage\LocalStorage;
use Misi\Storage\StorageInterface;
use Misi\Support\Config;
use Misi\Support\Env;
use Misi\Support\Session;
use Misi\Validation\Validator;
use Throwable;

/**
 * Punto de orquestación de Misi.
 *
 * Responsabilidades (Fase 1-10):
 *  1. Cargar variables de entorno.
 *  2. Cargar configuración.
 *  3. Exponer Router, Database, Validator, Session, Storage y Logger a
 *     la aplicación.
 *  4. Registrar los middleware por defecto (auth/guest/csrf).
 *  5. Descubrir módulos (modules/{Nombre}/module.php) y enganchar sus rutas.
 *  6. Ejecutar el request/response cycle.
 *  7. Manejar errores de forma centralizada, registrando en el log los
 *     que representan un problema real del sistema (5xx).
 *
 * NO es un Service Container complejo. Si en el futuro se necesita
 * inyección de dependencias más avanzada, se evalúa (ver ROADMAP).
 */
final class Application
{
    public readonly Config $config;
    public readonly Router $router;
    private ?Database $database = null;
    private ?StorageInterface $storage = null;
    private ?Logger $logger = null;

    /** @var array<int, array{name: string, routes: string|null, migrations: string|null}> */
    private array $modules = [];

    private static ?self $instance = null;

    public function __construct(public readonly string $basePath)
    {
        Env::load($this->basePath . '/.env');
        $this->config = new Config($this->basePath . '/config');
        $this->router = new Router();

        $this->configureErrorReporting();
        $this->configureSession();
        $this->registerDefaultMiddleware();
        $this->discoverModules();

        self::$instance = $this;
    }

    /**
     * Instancia actual de Application, usada por el helper global app().
     *
     * El Router instancia controladores con `new $class()` (sin
     * contenedor de inyección de dependencias, ver docs/architecture.md
     * "Riesgos técnicos"), así que este acceso estático es la forma
     * mínima de que un controlador llegue a Database/Validator sin
     * boilerplate. Si en el futuro esto se vuelve insuficiente, se
     * evalúa un contenedor pequeño (no antes).
     */
    public static function current(): self
    {
        return self::$instance ?? throw new \RuntimeException('Application aún no ha sido inicializada.');
    }

    private function configureErrorReporting(): void
    {
        $debug = (bool) $this->config->get('app.debug', false);
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting(E_ALL);

        $timezone = $this->config->get('app.timezone', 'UTC');
        date_default_timezone_set((string) $timezone);
    }

    /**
     * En CLI (bin/biz) no hay cookies que enviar ni cabeceras HTTP reales:
     * configurar nombre/parámetros de cookie de sesión ahí no tiene efecto
     * útil y, además, PHP emite un warning ("session cookie parameters
     * cannot be changed after headers have already been sent") en cuanto
     * un comando imprime algo antes de instanciar Application (ej.
     * "doctor", "info") — detectado al integrar los nuevos comandos de la
     * CLI. Ningún comando de bin/biz necesita sesión de todas formas
     * (Auth::check() vía sesión no aplica a un proceso CLI de un solo
     * request), así que se omite por completo en ese SAPI.
     */
    private function configureSession(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        Session::configure(
            name: (string) $this->config->get('session.cookie_name', 'misi_session'),
            lifetime: (int) $this->config->get('session.lifetime', 0),
            secure: (bool) $this->config->get('session.secure', false),
            sameSite: (string) $this->config->get('session.same_site', 'Lax'),
        );
    }

    /**
     * Escanea modules/{Nombre}/module.php y guarda su descriptor. Cada
     * módulo declara su propio nombre, ruta a routes.php (o null) y
     * carpeta de migraciones (o null) — Application no asume nada sobre
     * la estructura interna del módulo más allá de ese contrato mínimo.
     *
     * No hay sistema de eventos/hooks: un módulo solo puede "engancharse"
     * agregando rutas y migraciones. Si en el futuro varios módulos
     * reales necesitan reaccionar a eventos de otros, se evalúa entonces
     * (ver docs/modules.md).
     */
    private function discoverModules(): void
    {
        $modulesDir = $this->basePath . '/modules';

        if (!is_dir($modulesDir)) {
            return;
        }

        $descriptorFiles = glob($modulesDir . '/*/module.php') ?: [];
        sort($descriptorFiles);

        foreach ($descriptorFiles as $file) {
            $descriptor = require $file;

            if (!is_array($descriptor) || !isset($descriptor['name']) || !is_string($descriptor['name'])) {
                throw new \RuntimeException("module.php inválido (falta 'name'): {$file}");
            }

            $this->modules[] = [
                'name' => $descriptor['name'],
                'routes' => isset($descriptor['routes']) && is_file((string) $descriptor['routes'])
                    ? (string) $descriptor['routes']
                    : null,
                'migrations' => isset($descriptor['migrations']) && is_dir((string) $descriptor['migrations'])
                    ? (string) $descriptor['migrations']
                    : null,
            ];
        }
    }

    /** @return array<int, array{name: string, routes: string|null, migrations: string|null}> */
    public function modules(): array
    {
        return $this->modules;
    }

    /**
     * Middleware 'auth', 'guest' y 'csrf' disponibles desde el día uno
     * para cualquier ruta (`$router->get('/perfil', [...], ['auth'])`).
     * Cada proyecto puede sobrescribirlos llamando de nuevo a
     * aliasMiddleware() con el mismo nombre si necesita otro
     * comportamiento (ej. redirigir a /login en vez de devolver JSON).
     */
    private function registerDefaultMiddleware(): void
    {
        $this->router->aliasMiddleware('auth', function (Request $request, callable $next) {
            if (!Auth::check()) {
                throw new AuthenticationException();
            }

            return $next($request);
        });

        $this->router->aliasMiddleware('guest', function (Request $request, callable $next) {
            if (Auth::check()) {
                throw new AuthorizationException('Ya hay una sesión activa.');
            }

            return $next($request);
        });

        $this->router->aliasMiddleware('csrf', function (Request $request, callable $next) {
            // Métodos de solo lectura no mutan estado: no tiene sentido
            // exigirles token CSRF.
            if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
                return $next($request);
            }

            if (!Csrf::validate($request)) {
                // 419 (convención de Laravel para "página expirada" /
                // token CSRF ausente o inválido) — se distingue de un 401
                // (no autenticado) y un 403 (autenticado pero sin
                // permiso): aquí el problema es específicamente el token.
                return JsonResponse::error('Token CSRF inválido o ausente.', [], 419);
            }

            return $next($request);
        });
    }

    public function database(): Database
    {
        if (!$this->database instanceof Database) {
            $this->database = new Database($this->config->get('database', []));
        }

        return $this->database;
    }

    /**
     * Crea un Validator listo para usar. Si el proyecto no necesita las
     * reglas 'unique'/'exists' (que consultan la base de datos), esto
     * funciona igual sin haber configurado ninguna conexión.
     */
    public function validator(): Validator
    {
        return new Validator($this->database());
    }

    /**
     * Storage configurado según config/storage.php. Solo 'local' está
     * implementado (LocalStorage) — S3Storage/CloudStorage son 🧊
     * congelados hasta que un proyecto real los necesite (ver
     * docs/storage.md).
     */
    public function storage(): StorageInterface
    {
        if (!$this->storage instanceof StorageInterface) {
            $driver = (string) $this->config->get('storage.driver', 'local');

            if ($driver !== 'local') {
                throw new \RuntimeException(
                    "Driver de storage no soportado: '{$driver}'. Solo 'local' está implementado por ahora."
                );
            }

            $path = (string) $this->config->get('storage.path', 'storage/uploads');
            $root = str_starts_with($path, '/') ? $path : $this->basePath . '/' . $path;

            $this->storage = new LocalStorage($root);
        }

        return $this->storage;
    }

    /** Logger configurado según config/logging.php (nivel mínimo + carpeta). */
    public function logger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $path = (string) $this->config->get('logging.path', 'storage/logs');
            $root = str_starts_with($path, '/') ? $path : $this->basePath . '/' . $path;

            $this->logger = new Logger($root, (string) $this->config->get('logging.level', 'debug'));
        }

        return $this->logger;
    }

    /**
     * Carga el archivo de rutas principal (routes/web.php) y, a
     * continuación, el routes.php de cada módulo descubierto — en ambos
     * casos exponiéndoles $router para que registren sus rutas con la
     * misma API (`$router->get(...)`, etc.).
     */
    public function loadRoutes(string $routesFile): void
    {
        $router = $this->router;
        require $routesFile;

        foreach ($this->modules as $module) {
            if ($module['routes'] !== null) {
                require $module['routes'];
            }
        }
    }

    public function run(): void
    {
        $request = Request::capture();

        try {
            $response = $this->router->dispatch($request);
        } catch (Throwable $e) {
            $response = $this->handleException($e);
        }

        $this->applyDefaultSecurityHeaders($response, $request);
        $response->send();
    }

    /**
     * Cabeceras de seguridad que docs/security.md marcaba como
     * pendientes hasta esta fase ("no tiene sentido configurarlas
     * mientras la superficie de rutas/vistas seguía cambiando fase a
     * fase"). Se aplican en un único lugar a TODA respuesta (éxito y
     * error, HTML y JSON) para que ningún controlador tenga que
     * acordarse de ponerlas.
     *
     * `hasHeader()` evita pisar una cabecera que el controlador ya fijó
     * a propósito — estos son defaults, no valores forzados.
     *
     * Deliberadamente NO incluye Content-Security-Policy: un CSP
     * genérico razonable rompería cualquier proyecto con <script>
     * inline sin nonce (el propio examples/demo-app los usa
     * extensivamente, ver resources/views/app.php) — el valor correcto
     * depende de cada frontend, así que no hay un default seguro que no
     * sea "romper cosas" o "no proteger nada". Cada proyecto que lo
     * necesite lo agrega en su propio middleware/controlador según su
     * propia superficie de scripts/estilos.
     */
    private function applyDefaultSecurityHeaders(Response $response, Request $request): void
    {
        $defaults = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ];

        foreach ($defaults as $name => $value) {
            if (!$response->hasHeader($name)) {
                $response->header($name, $value);
            }
        }

        if ($request->isSecure() && !$response->hasHeader('Strict-Transport-Security')) {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }

    private function handleException(Throwable $e): Response
    {
        $debug = (bool) $this->config->get('app.debug', false);

        if ($e instanceof ValidationException) {
            // Input inválido del usuario: parte normal del flujo, no se
            // registra en el log (evita ensuciarlo con "ruido" esperado).
            return JsonResponse::error($e->getMessage(), $e->getErrors(), $e->getStatusCode());
        }

        if ($e instanceof HttpException) {
            // Solo se registran los errores que indican un problema real
            // del sistema (5xx). Un 404/401/403/419/422 es parte normal
            // del tráfico de cualquier aplicación, no un fallo a investigar.
            if ($e->getStatusCode() >= 500) {
                $this->logException('error', $e);
            }

            $message = $e->getMessage() !== '' ? $e->getMessage() : 'Error';
            return JsonResponse::error($message, [], $e->getStatusCode());
        }

        // Excepción no controlada: por definición algo que el código no
        // anticipó, siempre se registra como crítica.
        $this->logException('critical', $e);

        if ($debug) {
            return new Response(
                "<h1>Error 500</h1><pre>" . htmlspecialchars((string) $e) . "</pre>",
                500
            );
        }

        return new Response('Internal Server Error', 500);
    }

    /**
     * Registra la excepción con clase, archivo y línea. Deliberadamente
     * NO incluye el stack trace completo (getTraceAsString()): en PHP,
     * los argumentos de cada frame del trace pueden contener valores
     * sensibles (ej. la contraseña pasada a Auth::attempt()) — Logger ya
     * redacta claves de contexto conocidas, pero un trace es texto libre
     * que no puede sanearse de la misma forma. Ver docs/logging.md.
     */
    private function logException(string $level, Throwable $e): void
    {
        $context = [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($e->getPrevious() !== null) {
            $context['caused_by'] = get_class($e->getPrevious()) . ': ' . $e->getPrevious()->getMessage();
        }

        $this->logger()->{$level}($e->getMessage(), $context);
    }
}
