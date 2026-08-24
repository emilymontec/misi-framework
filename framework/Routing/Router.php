<?php

declare(strict_types=1);

namespace Misi\Routing;

use Misi\Exceptions\NotFoundException;
use Misi\Http\Request;
use Misi\Http\Response;

/**
 * Router HTTP minimalista.
 *
 * Soporta GET/POST/PUT/PATCH/DELETE, parámetros de ruta {id}
 * y middleware básico (alias -> callable).
 *
 * No implementa: route caching, subdominios, named routes con generación
 * de URL avanzada, ni resolución de controladores vía container complejo.
 * Si el proyecto lo necesita en el futuro, se evalúa agregarlo (ver ROADMAP).
 */
final class Router
{
    /** @var array<int, Route> */
    private array $routes = [];

    /** @var array<string, callable(Request, callable): Response> */
    private array $middlewareAliases = [];

    public function get(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $uri, $handler, $middleware);
    }

    public function post(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $uri, $handler, $middleware);
    }

    public function put(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $uri, $handler, $middleware);
    }

    public function patch(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $uri, $handler, $middleware);
    }

    public function delete(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $uri, $handler, $middleware);
    }

    private function addRoute(string $method, string $uri, mixed $handler, array $middleware): void
    {
        $this->routes[] = new Route($method, $uri, $handler, $middleware);
    }

    /** Registra un middleware con un alias, ej: $router->aliasMiddleware('auth', ...) */
    public function aliasMiddleware(string $alias, callable $middleware): void
    {
        $this->middlewareAliases[$alias] = $middleware;
    }

    /**
     * Rutas registradas, en el orden en que fueron declaradas. De solo
     * lectura: pensado para introspección (ej. `php bin/biz route:list`),
     * no para que nada fuera del Router las mute.
     *
     * @return array<int, Route>
     */
    public function routes(): array
    {
        return $this->routes;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            $params = $route->match($uri);

            if ($params === null) {
                continue;
            }

            return $this->runRoute($route, $request, $params);
        }

        throw new NotFoundException("Ruta no encontrada: {$method} {$uri}");
    }

    /** @param array<string, string> $params */
    private function runRoute(Route $route, Request $request, array $params): Response
    {
        $pipeline = array_reverse($route->middleware);

        $core = function (Request $request) use ($route, $params): Response {
            return $this->callHandler($route->handler, $request, $params);
        };

        $next = $core;

        foreach ($pipeline as $alias) {
            $middleware = $this->middlewareAliases[$alias]
                ?? throw new \RuntimeException("Middleware no registrado: {$alias}");

            // Contrato (ver docs/routing.md "Middleware"): un middleware
            // SIEMPRE debe devolver un Response propio (para cortar la
            // cadena) o el resultado de `return $next($request)` (para
            // continuar). No existe un tercer caso "null = continuar".
            //
            // Antes esta línea tenía `?? $core($request)`: si un
            // middleware olvidaba el `return` antes de `$next($request)`
            // (typo fácil), su valor de retorno implícito era `null`, y
            // ese `??` saltaba directo al controlador, saltándose
            // CUALQUIER middleware intermedio entre ese y el handler
            // (por ejemplo 'csrf'). Ahora, si un middleware no cumple el
            // contrato, el type hint `: Response` de este closure lanza
            // un TypeError inmediato y ruidoso en vez de abrir un hueco
            // de seguridad silencioso.
            $next = fn (Request $request): Response => $middleware($request, $next);
        }

        return $next($request);
    }

    /** @param array<string, string> $params */
    private function callHandler(mixed $handler, Request $request, array $params): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return $controller->{$method}($request, ...array_values($params));
        }

        return $handler($request, ...array_values($params));
    }
}
