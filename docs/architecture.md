# Arquitectura de Misi (Fase 0 — Diseño)

Este documento resume las decisiones de arquitectura tomadas para Misi y
sirve como referencia para todas las fases posteriores.

## 1. Arquitectura por capas

```text
                    Misi
                       │
        ┌──────────────┴──────────────┐
        │                              │
    FRAMEWORK                    BUSINESS CORE
   (namespace Misi\)             (futuro, capa
        │                          separada, no
        │                          mezclada con
        │                          el framework)
        │
        └──────────────┬──────────────┘
                       │
                    MODULES
                (namespace Modules\)
                       │
              ┌────────┼────────┐
              ↓        ↓        ↓
            Ropa   Bordados   Inventario
                       │
                       ↓
                  APLICACIÓN
               (namespace App\, un
                proyecto concreto)
```

- **Framework** (`framework/`, namespace `Misi\`): herramientas técnicas
  puras. No sabe qué es un "cliente" ni un "pedido".
- **Business Core** (futuro `business/` o `core/`): funcionalidades
  administrativas reutilizables (clientes, productos, pedidos, pagos).
  Se construye **sobre** el framework, nunca dentro de él.
- **Modules** (`modules/`, namespace `Modules\`): funcionalidades específicas
  de un tipo de negocio (ropa, bordados, inventario especializado, etc.).
- **Application** (`app/`, namespace `App\`): el proyecto concreto para un
  cliente real, que combina framework + business core + módulos + su propia
  personalización.

Regla dura: **el framework nunca importa nada de `App\`, `Modules\` ni de
una futura capa de Business Core.** La dependencia siempre va hacia adentro
(la aplicación depende del framework, nunca al revés).

## 2. Flujo de ejecución de una request

```text
1. public/index.php
      → requiere bootstrap/app.php

2. bootstrap/app.php
      → requiere bootstrap/autoload.php (Composer o autoload manual)
      → crea Misi\Core\Application
      → Application::__construct():
            - Env::load('.env')
            - crea Config (lee config/*.php)
            - crea Router
            - configura error_reporting / timezone según config
      → Application::loadRoutes('routes/web.php')
            - el archivo de rutas recibe $router y registra rutas

3. public/index.php
      → $app->run()

4. Application::run()
      → Request::capture()   (lee superglobales una sola vez)
      → Router::dispatch($request)
            - busca la primera Route cuyo método + patrón coincidan
            - si no hay match → NotFoundException (404)
            - si hay match → ejecuta pipeline de middleware → controlador
      → controlador devuelve un Response (o JsonResponse/RedirectResponse)
      → si algo lanza una excepción, Application::handleException()
        la traduce a una Response coherente (JSON para HttpException/
        ValidationException, HTML con detalle solo si APP_DEBUG=true)

5. $response->send()
      → http_response_code()
      → headers
      → echo del contenido
```

Ningún paso oculto: no hay "magia" de reflexión pesada, no hay contenedor de
inyección de dependencias complejo. El controlador se instancia directamente
(`new $class()`), lo cual es suficiente para el tamaño de proyecto objetivo.
Si en el futuro un proyecto necesita inyectar dependencias en constructores
de controladores, se evalúa agregar un contenedor **pequeño y explícito**
(ver sección "Riesgos" y `ROADMAP.md`).

## 3. Responsabilidad de cada capa

| Capa | Responsabilidad | Lo que NO hace |
|------|------------------|------------------|
| `Misi\Support` | Env, Config | No conoce HTTP ni DB |
| `Misi\Http` | Request, Response, JsonResponse, RedirectResponse | No enruta, no valida negocio |
| `Misi\Routing` | Matching de rutas, parámetros, pipeline de middleware | No conoce controladores de negocio específicos |
| `Misi\Database` | Conexión PDO, prepared statements, transacciones | No es ORM, no genera SQL complejo |
| `Misi\Auth` (futuro) | Sesión de usuario autenticado, hashing | No define roles de negocio específicos del cliente |
| `Misi\Security` (futuro) | CSRF, sanitización | No define reglas de autorización de negocio |
| `Misi\Validation` | Reglas de validación reutilizables | No conoce las reglas de negocio del cliente |
| `Misi\Storage` (futuro) | Abstracción de almacenamiento de archivos | No decide qué se sube (eso es de la app) |
| `Misi\Logging` (futuro) | Registro de eventos técnicos | No es un sistema de auditoría de negocio |
| `Misi\Core` | Orquesta todo lo anterior (`Application`) | No contiene lógica de negocio |
| `App\Http\Controllers` | Recibe Request, delega a Service, devuelve Response | No contiene SQL ni reglas de negocio complejas |
| `App\Services` (futuro por proyecto) | Lógica de aplicación/negocio | No conoce detalles HTTP |
| `App\Repositories` (futuro por proyecto) | Acceso a datos vía `Database` | No conoce HTTP ni reglas de negocio |

## 4. Sistema de configuración

- `.env` (no versionado) define valores por entorno (credenciales,
  debug, etc.). `.env.example` sí se versiona como plantilla.
- `Misi\Support\Env` parsea el `.env` sin dependencias externas
  (evita agregar `vlucas/dotenv` solo para esto: el parseo necesario es
  simple y así Misi funciona sin `composer install`).
- `config/*.php` son archivos PHP que devuelven arrays y **leen** de `Env`.
  Cada archivo es una clave raíz: `config/database.php` se accede como
  `$app->config->get('database.host')`.
- Nunca se hardcodean credenciales en el código: siempre `Env::get(...)`
  dentro de `config/*.php`, nunca directamente en clases del framework.

## 5. Diseño del Router

- Rutas registradas por método HTTP: `get/post/put/patch/delete`.
- Parámetros de ruta con sintaxis `{param}`, convertidos internamente a
  regex (`([^/]+)`).
- Middleware: alias → callable, registrado con `aliasMiddleware()`.
  Cada ruta declara qué alias de middleware requiere. El pipeline se
  construye con un patrón "onion" simple (sin librería externa).
- **Explícitamente fuera de alcance por ahora**: route caching, grupos de
  rutas con prefijos anidados complejos, subdominios, rutas nombradas con
  generación de URLs. Se agregan solo si un proyecto real lo pide (ver
  ROADMAP Fase 3.1).

## 6. Diseño de Request/Response

- `Request` es inmutable y se construye **una vez** por request
  (`Request::capture()`), leyendo `$_GET`, `$_POST`, `$_SERVER`,
  `$_COOKIE`, `$_FILES` y el body crudo (para soportar JSON).
- `Response` es la clase base; `JsonResponse` y `RedirectResponse` la
  extienden. El formato JSON estándar de éxito/error está fijado desde el
  inicio (ver `README.md` y `docs/http.md` cuando se agregue en Fase 2.1).

## 7. Diseño de Database

- `Misi\Database\Database` envuelve PDO: conexión perezosa y reutilizada,
  `query()`, `select()`, `selectOne()`, `insert()`, `update()`, `delete()`,
  `transaction()`.
- 100% prepared statements. Ningún método concatena input del usuario en
  el SQL.
- **No se construye un ORM.** Si en el futuro la repetición de código lo
  justifica, se evalúa un Query Builder pequeño (Fase 4.1), pero con un
  límite explícito de alcance: si empieza a necesitar joins complejos,
  scopes, eager loading, etc., **se detiene** — eso ya es terreno de
  Eloquent/Doctrine y no es el objetivo de Misi.

## 8. Diseño de Migrations y Seeders (implementado, Fase 4.2/4.3)

**Migrations:**

- Migraciones como archivos PHP numerados en `database/migrations/`
  (ej. `001_create_users_table.php`). El prefijo numérico define el orden
  de ejecución.
- Cada archivo hace `return new class extends Misi\Database\Migration { ... }`
  con `up()` y `down()`. `Migration` no recibe la conexión por constructor:
  el `Migrator` la inyecta después vía `setDatabase()`, para que instanciar
  la clase anónima no dependa de pasar argumentos al `new class`.
- `Misi\Database\Migrator` centraliza la lógica: `run()` (ejecuta
  pendientes y registra el batch), `rollback()` (revierte el último batch
  completo, en orden inverso), `status()` (compara archivos en disco contra
  lo registrado en la tabla `migrations`).
- La tabla `migrations` (columnas: `migration`, `batch`, `run_at`) se
  autocrea con `CREATE TABLE IF NOT EXISTS` — no requiere una migración
  propia ni configuración adicional.
- No se usa un DSL de definición de esquema (`Schema::create(...)`): los
  `up()/down()` ejecutan SQL crudo a través de `Database::query()`.
  Simplicidad ante todo — sigue siendo legible para una desarrolladora
  junior/intermedia, y evita reinventar un ORM.

**Seeders:**

- `Misi\Database\Seeder` sigue el mismo patrón que `Migration`
  (`setDatabase()` inyectado externamente, no por constructor).
- `database/seeders/DatabaseSeeder.php` es el punto de entrada convencional
  (ejecutado por `db:seed`). Para proyectos con varios seeders, este archivo
  es el lugar natural para orquestarlos (llamando a otros seeders desde su
  `run()`), sin necesidad de un mecanismo de descubrimiento automático.
- El seeder de ejemplo crea un usuario admin demo con
  `password_hash()` (nunca texto plano) y **verifica antes de insertar**
  para poder ejecutarse más de una vez sin duplicar datos ni fallar por
  restricción `UNIQUE`.

**Runner (histórico, Fase 4 → consolidado en Fase 11):**

- En la Fase 4 se creó un punto de entrada mínimo (`migrate`,
  `migrate:rollback`, `migrate:status`, `db:seed`) deliberadamente
  pequeño en `bin/console.php`: lo mínimo necesario para que
  Migrations/Seeders fueran utilizables ya, explícitamente marcado como
  no ser todavía el CLI completo. En la Fase 11 ese archivo se eliminó y
  sus comandos se consolidaron en `bin/biz` (ver sección 15) — tal como
  se planeó desde el principio.
- Probado de punta a punta contra MariaDB real (no solo linting): crear
  tabla, verificar estado, sembrar datos con detección de duplicados,
  y revertir limpiamente.

## 9. Diseño de Validation (implementado, Fase 5)

- `Misi\Validation\Validator`, con una única API pública: `validate(array
  $data, array $rules): array`. Devuelve solo los campos que tenían regla
  definida (los demás campos del request se ignoran silenciosamente, igual
  que en la mayoría de frameworks — evita que el cliente inyecte campos no
  esperados).
- Reglas como strings simples con parámetros tras `:` (ej. `'max:150'`,
  `'in:borrador,publicado'`). No se implementa un DSL de objetos-regla
  (`Rule::max(150)`) para mantener la sintaxis compacta que ya pedía el
  diseño original — se puede añadir más adelante si algún proyecto lo
  necesita, sin romper la sintaxis de string existente.
- Reglas soportadas: `required, nullable, string, integer, numeric,
  boolean, email, url, date, min, max, in, unique, exists, file, image,
  mimes, max_size`.
- **Comportamiento de campos opcionales**: si un campo no tiene la regla
  `required` y llega vacío/ausente, se omite del resultado sin generar
  error — sin necesidad de declarar `nullable` explícitamente (aunque se
  recomienda declararlo por legibilidad, como en los ejemplos).
- **`min`/`max` son contextuales**: si el valor es un archivo subido,
  comparan el tamaño en KB; si la regla incluye `numeric`/`integer`,
  comparan el valor numérico; si es un string, comparan longitud
  (`mb_strlen`, no `strlen`, para no romper con acentos/UTF-8); si es un
  array, comparan la cantidad de elementos.
- **`unique`/`exists`** ejecutan una consulta real contra `Database`
  (prepared statement, nunca concatenación). `unique` soporta un cuarto y
  quinto parámetro opcionales (`unique:tabla,columna,valor,columna_excepcion`)
  para permitir el propio registro al validar una edición.
- **`file`/`image`/`mimes`/`max_size`** operan sobre la estructura de
  `$_FILES` (`tmp_name`, `error`, `size`, `name`). `image` verifica el MIME
  real vía `finfo`, no la extensión — la extensión sola es fácil de
  falsificar. Esto se coordina con la Fase 8 (Storage): Validation decide
  si el archivo es válido, Storage decide dónde y cómo guardarlo.
- **Errores estructurados**: cada campo inválido acumula uno o más
  mensajes en español (`{"campo": ["mensaje1", "mensaje2"]}`), lanzados
  como `ValidationException` (ya definida en Fase 1). `Application::handleException()`
  ya sabía traducir esta excepción a un 422 JSON desde la Fase 1 — Fase 5
  solo tuvo que empezar a lanzarla de verdad.
- **Helper global `app()`** (`framework/Support/helpers.php`): se adelantó
  desde la futura Fase de Helpers porque el Router instancia controladores
  sin contenedor de DI (`new $class()`). Sin `app()`, cada controlador que
  necesite `Validator` o `Database` tendría que reconstruirlos a mano
  repitiendo la configuración. Es deliberadamente el único helper global
  por ahora — el resto de helpers listados en el roadmap original
  (`asset()`, `url()`, `csrf_token()`, etc.) se agregan en sus fases
  correspondientes, no todos de golpe.
- No se implementa: mensajes de error personalizables por campo/regla,
  reglas condicionales (`required_if`, `required_with`), ni validación
  anidada de arrays complejos. Se agregan solo si un proyecto real los
  necesita.

## 10. Diseño de Auth (implementado, Fase 6)

- Basado en sesiones PHP nativas (`$_SESSION`), sin JWT ni OAuth por ahora
  (no hay necesidad real todavía; se evalúa solo si un proyecto lo requiere,
  por ejemplo una app móvil que consuma la misma API).
- `password_hash()` / `password_verify()` (bcrypt/argon2 vía PHP nativo).
- Regeneración de ID de sesión tras login (mitiga session fixation) —
  antes de guardar el usuario en sesión, no después.
- API estática: `Auth::attempt()`, `Auth::login()`, `Auth::logout()`,
  `Auth::check()`, `Auth::guest()`, `Auth::user()`, `Auth::id()`,
  `Auth::can()`.
- RBAC simple (Fase 6.1): tablas `roles`/`permissions`/`role_user`/
  `permission_role`, sin jerarquía ni permisos condicionales. Ver
  `docs/authentication.md` y `docs/authorization.md`.

## 11. Diseño de Security (implementado, Fase 7)

- CSRF: `Misi\Security\Csrf` con token único por sesión (no por
  request), comparado con `hash_equals()`. Helpers `csrf_token()`,
  `csrf_field()`, `csrf_validate()`. Middleware `csrf` registrado
  automáticamente por `Application`, que deja pasar `GET/HEAD/OPTIONS` sin
  exigir token (son de solo lectura) y acepta el token vía `_token` en el
  body o header `X-CSRF-Token` (fetch/AJAX). Un token inválido responde
  `419` — distinto de `401` (no autenticado) y `403` (sin permiso) — y no
  afecta la sesión activa.
- Autorización: sistema de roles/permisos simple (Fase 6.1), verificado
  siempre en backend — nunca se confía en el frontend para decisiones de
  autorización.
- Prevención activa de: SQL injection (prepared statements en todo),
  XSS (escape en el único output HTML dinámico existente hasta ahora;
  se revisa de nuevo cuando llegue un motor de vistas real en Fase 13),
  session fixation/hijacking (regeneración de sesión, cookies
  `HttpOnly`+`Secure` cuando `APP_ENV=production`, ya desde Fase 6).
  Path traversal y validación de uploads quedan explícitamente diferidos
  a Storage (Fase 8) — no hay archivos que proteger todavía. Ver el
  checklist completo en `docs/security.md`.

## 12. Diseño de Storage (implementado, Fase 8)

- `StorageInterface` con `put/putUploadedFile/get/delete/exists/url/size/mimeType`.
- `LocalStorage` como única implementación. La interfaz ya prepara el
  terreno para `S3Storage`/`CloudStorage` futuros, **sin implementarlos
  todavía** (🧊 congelado — no hay necesidad real hoy; agregarlo ahora
  sería sobreingeniería).
- Los archivos nunca se guardan en MySQL como BLOB; solo se guarda la
  metadata (ruta, mimetype, tamaño, propietario) — ver la migración de
  ejemplo `003_create_uploads_table.php`.
- `putUploadedFile()` centraliza la seguridad de subidas: nombre generado
  (`bin2hex(random_bytes(16))`, nunca el original), extensión saneada,
  verificación de `is_uploaded_file()` (defensa en profundidad, no confía
  ciegamente en que `Validation` ya lo comprobó), permisos `0644`
  (nunca ejecutable).
- `storage/uploads/` vive **fuera** del document root público (`public/`)
  a propósito: aunque un archivo subido fuera malicioso, el servidor web
  no puede ejecutarlo directamente por URL. El acceso pasa siempre por una
  ruta controlada por el proyecto (`GET /storage/{path*}` en la demo),
  que solo devuelve bytes con su `Content-Type` real — nunca hace
  `include`/`require` sobre contenido subido por un usuario.
- Bloqueo de path traversal en `LocalStorage::fullPath()`: cualquier ruta
  con `..` se rechaza (`StorageException`, 422), tanto al guardar como al
  leer. Probado explícitamente contra un intento real de
  `../../../../etc/passwd`.
- Se agregó soporte de parámetro de ruta "catch-all" (`{path*}` → `(.+)`)
  al Router (Fase 3) porque Storage lo necesitaba de verdad para servir
  archivos con subdirectorios (`avatars/xxxx.png`) — no es una
  abstracción especulativa, resolvió una necesidad concreta de esta fase.
- Nota de desarrollo: el servidor embebido de PHP (`php -S`) intenta
  servir como archivo estático cualquier URL con una extensión conocida
  (`.png`, `.css`...), devolviendo su propio 404 antes de llegar a
  `index.php` si el archivo no existe físicamente en `public/`. La
  solución definitiva (`bin/server.php` como router script, no
  `public/index.php` directamente) se terminó de resolver en la Fase 13
  — ver sección 17 — cuando aparecieron assets estáticos reales que
  expusieron que la solución inicial de esta fase era incompleta. No
  afecta a
  Apache/Nginx en producción. Ver `docs/storage.md`.

## 13. Diseño de Logging y manejo de errores (implementado, Fase 9)

- `Misi\Logging\Logger`: niveles `debug/info/warning/error/critical`,
  un archivo por día (`storage/logs/misi-YYYY-MM-DD.log`), sin
  dependencias externas ni necesidad de `logrotate`.
- **Filtro por relevancia, no solo por nivel.** `Application::handleException()`
  decide qué excepciones se registran: `ValidationException` y cualquier
  `HttpException` con status < 500 (404, 401, 403, 419, 422) son tráfico
  normal de cualquier aplicación y NO se registran — solo lo que
  representa un problema real del sistema (5xx, o cualquier excepción no
  controlada) llega al log. Esto evita el problema típico de logs
  inservibles llenos de ruido esperado.
- **Redacción automática y recursiva** de claves de contexto sensibles
  (`password`, `token`, `secret`, `api_key`, `csrf`, etc.) — protege
  contra loguear accidentalmente credenciales al pasar arrays de
  contexto, pero no puede proteger un mensaje mal escrito que interpole
  un valor sensible directamente como string (responsabilidad de quien
  llama al Logger, documentado explícitamente en `docs/logging.md`).
- **Nunca se registra el stack trace completo** (`getTraceAsString()`):
  en PHP los argumentos de cada frame pueden incluir valores sensibles
  (ej. el password pasado a `Auth::attempt()`), y un trace es texto libre
  que la redacción de claves no puede sanear. Se registra clase, mensaje,
  archivo y línea — suficiente para ubicar el problema sin ese riesgo.
- **`DatabaseException`, `AuthenticationException`, `AuthorizationException`**
  se suman a las excepciones tipadas de Fase 1 (`HttpException`,
  `NotFoundException`, `ValidationException`) y Fase 8 (`StorageException`).
  `Database::connection()` envuelve cualquier fallo de PDO en
  `DatabaseException` con mensaje genérico hacia el cliente, conservando
  el error original vía `getPrevious()` para que el log tenga el detalle
  completo sin que la respuesta HTTP lo exponga — probado explícitamente
  forzando una conexión con credenciales incorrectas: la contraseña real
  nunca aparece ni en la respuesta ni en el log.
- El middleware `auth`/`guest` (Fase 6) ahora lanza
  `AuthenticationException`/`AuthorizationException` en vez de construir
  `JsonResponse::error()` a mano — centraliza el mensaje/status en la
  excepción, consistente con `NotFoundException`/`ValidationException`.

## 14. Diseño de Modules (implementado, Fase 10)

Estructura de un módulo:

```text
modules/NombreModulo/
├── Controllers/
├── Services/
├── Repositories/
├── Models/
├── Views/
├── migrations/
├── routes.php
└── module.php   # contrato mínimo: name, routes, migrations
```

- **Descubrimiento sin cambios en el core**: `Application::discoverModules()`
  escanea `modules/*/module.php` al arrancar. Cada descriptor declara
  `name` (obligatorio, único), `routes` (ruta a un archivo o `null`) y
  `migrations` (ruta a una carpeta o `null`) — ese es todo el contrato,
  deliberadamente mínimo.
- **Rutas**: `Application::loadRoutes()` carga `routes/web.php` y después
  el `routes.php` de cada módulo, exponiéndoles el mismo `$router` — un
  módulo no tiene una API de routing distinta a la de `app/`.
- **Migraciones**: `Migrator` acepta múltiples fuentes (`array<{label,
  path}>`) en vez de una sola carpeta. Las del core mantienen su
  identificador sin prefijo (compatibilidad con lo ya ejecutado antes de
  esta fase); las de cada módulo se prefijan con su `name`
  (`"Inventory/001_....php"`) para evitar colisiones entre módulos que
  numeren igual. `bin/biz` arma esa lista de fuentes leyendo
  `$app->modules()`.
- **Namespace `Modules\`**: mapeado en `composer.json` y en el autoload
  de respaldo sin Composer — un controlador de módulo usa exactamente las
  mismas herramientas (`app()->database()`, `Validator`, etc.) que uno de
  `app/`.
- **Módulo de referencia**: `modules/Example/` es un módulo real y
  funcional (no un stub) que demuestra el mecanismo completo — pensado
  para copiarse como punto de partida al crear el primer módulo de
  negocio real, y para no necesitar creer en la palabra del roadmap sin
  verlo correr.
- **Deliberadamente sin sistema de eventos/hooks**: un módulo solo puede
  "engancharse" agregando rutas y migraciones. Si varios módulos reales
  necesitan comunicarse entre sí, se evalúa entonces — no antes.
- **Orden entre módulos**: alfabético por nombre de carpeta. No hay
  resolución de dependencias declaradas entre módulos; si el orden
  importa, un prefijo numérico en el nombre de carpeta resuelve el caso
  simple sin que el framework necesite un resolutor de grafos.

Ver `docs/modules.md` para la guía de uso completa.

## 15. Diseño del CLI (implementado, Fase 11)

- Un único punto de entrada `bin/biz` (script PHP ejecutable, shebang
  `#!/usr/bin/env php`) que despacha a comandos con un `match($comando)`
  simple — nada de registro de clases ni autodescubrimiento.
- Sin dependencia de Symfony Console ni similares: los comandos
  necesarios (parseo de `$argv`, imprimir a stdout) fueron simples de
  resolver a mano. Se reevalúa solo si el CLI crece mucho en complejidad
  (más comandos con opciones complejas, necesidad real de autocompletado,
  etc.) — no antes.
- Consolida lo que hasta la Fase 10 vivía en `bin/console.php` (un
  "runner mínimo" explícitamente temporal) — ese archivo se elimina en
  esta fase, no convive con `bin/biz`.
- `serve` no es un wrapper trivial de `php -S`: usa `bin/server.php`
  (no `public/index.php` directamente) como router script — ver sección
  17 (Frontend/UI utilities) para el detalle completo de por qué existe
  ese archivo separado. Sin él, cada desarrollador tendría que recordar
  el flag manualmente y además chocaría con el problema de servir
  CSS/JS reales (Fase 13) que llevó a descubrir que "pasar
  `public/index.php` como router script" (la solución original de la
  Fase 8) era incompleta.
- Los comandos `make:*` (Fase 12) se agregaron a este mismo archivo — no
  se creó un segundo punto de entrada para ellos.
- `serve` y `make:*` deliberadamente **no** instancian `Application`: no
  tiene sentido exigir un `.env` con credenciales de base de datos
  válidas solo para levantar el servidor o generar un archivo.

## 16. Diseño de los Generadores (implementado, Fase 12)

- `resources/stubs/*.stub`: plantillas de texto plano con placeholders
  `{{clave}}`, reemplazados con `str_replace()`. Nada de construcción
  programática de AST ni de dependencias de generación de código — el
  roadmap pedía explícitamente "código limpio y entendible", y lo que se
  ve en el `.stub` es exactamente lo que se genera.
- Cinco comandos, calcados de las capas ya definidas en la arquitectura
  (sección 3): `make:controller`, `make:model`, `make:service`,
  `make:repository`, `make:module`. No se agregan más (`make:migration`,
  `make:middleware`, etc.) hasta que copiar un archivo de ejemplo a mano
  empiece a doler de verdad en varios proyectos reales.
- **Ninguno sobrescribe silenciosamente.** Si el archivo (o, para
  `make:module`, el directorio) de destino ya existe, el comando falla
  explícitamente — evita que un desarrollador pierda trabajo por
  accidente al re-ejecutar un comando.
- `make:controller` soporta subcarpetas vía `/` (`Api/Ping` →
  `App\Http\Controllers\Api`, clase `PingController`), reflejando la
  convención que ya usaban los controladores de demo desde fases
  anteriores (`app/Http/Controllers/Api/`).
- `make:model`/`make:repository` adivinan el nombre de tabla con una
  pluralización heurística en inglés (sufijos `s`/`es`/`ies` según
  terminación) — suficiente para los casos comunes (`Product` →
  `products`, `Category` → `categories`, `Business` → `businesses`), sin
  pretender ser lingüísticamente perfecta. El comando siempre imprime el
  nombre de tabla asumido para que se ajuste a mano si no coincide con la
  migración real — nunca falla en silencio por adivinar mal.
- `make:module` genera la estructura completa pero **vacía** (sin
  controlador ni migración de ejemplo dentro): `modules/Example/`, ya
  incluido en el proyecto desde la Fase 10, cumple el rol de referencia
  con contenido funcional — no hace falta que cada módulo nuevo lo
  reinvente.

## 17. Diseño de Frontend / UI utilities (implementado, Fase 13)

- `public/css/misi.css`: design tokens vía variables CSS en `:root`
  (colores, tipografía, radios, sombras) + 8 componentes con prefijo
  `sd-` (buttons, forms, tables, alerts, modals, navigation, pagination,
  cards) — exactamente lo que pedía el roadmap, sin crecer hacia un
  framework CSS completo (nada de grid system ni utilidades tipo
  Tailwind).
- `public/js/api.js`: `apiFetch()` + fachada `api.{get,post,put,patch,delete,upload}`
  sobre `fetch()` nativo, sin axios. Entiende el formato de respuesta
  estándar de Misi (`docs/http.md`) y obtiene el token CSRF
  automáticamente (cacheado en memoria) para toda petición mutante —
  probado con una ejecución real del script en Node contra un servidor
  Misi vivo (no solo revisión visual del código), incluyendo el
  formato de error 422 con `errors` poblado tal cual lo entrega
  `ValidationException`.
- `public/js/ui.js`: `showAlert()`, `confirmAction()`, `modal()`,
  `formSubmit()` — sin dependencias, operando sobre las clases de
  `misi.css`.
- **Decisión estructural deliberada**: los assets viven en `public/`, no
  en `resources/css`/`resources/js` como sugería el diagrama original
  del roadmap. Sin build step (Misi evita Node.js en producción a
  propósito), mantenerlos en `resources/` habría significado servirlos
  vía una ruta de Misi (overhead de PHP en cada request de asset) o
  duplicarlos a mano en cada cambio. Se documentó explícitamente el
  cambio (regla del proyecto: "no cambios estructurales sin
  explicarlos") en `docs/frontend.md`.
- **Bug real descubierto y corregido en esta fase**: la solución de la
  Fase 8 para el servidor embebido de PHP ("pasar `public/index.php`
  como router script") resultó incompleta. Un router script recibe
  *todas* las peticiones, incluidas las de archivos estáticos reales —
  y `index.php` nunca le devuelve el control al servidor para que sirva
  uno, así que `/css/misi.css` real terminaba devolviendo el 404 JSON
  de Misi. Se creó `bin/server.php`, un router script dedicado
  exclusivamente al servidor embebido (nunca usado en producción) que
  verifica si la URL corresponde a un archivo real en `public/` y, de
  ser así, le devuelve el control al servidor (`return false;`) para que
  lo sirva tal cual. Verificado que ambos casos conviven correctamente:
  CSS/JS estáticos servidos directamente, y rutas dinámicas (incluida
  `/storage/{path*}`, que no es un archivo real en `public/`) siguiendo
  pasando por la aplicación.
- **Sin motor de plantillas.** `resources/views/ui-kit.php` es PHP plano
  incluido con output buffering — no se introduce Blade/Twig hasta que
  la repetición de HTML entre vistas de un proyecto real lo justifique.

## 18. Diseño de la Demo Application (implementado, Fase 14)

- `examples/demo-app/`: un taller de bordados con clientes y pedidos —
  caso de uso concreto, no datos abstractos tipo "foo/bar". Objetivo
  explícito del roadmap: demostrar todas las fases funcionando *juntas*,
  no cada una aislada como las demos por fase (`/ui-kit`,
  `/api/validate-demo`, etc.).
- **Reutiliza `framework/` por ruta relativa, no lo copia.** El
  `bootstrap/autoload.php` de la demo mapea `Misi\` a
  `dirname(__DIR__, 3) . '/framework/'` (la del proyecto padre); `App\` y
  `Modules\` sí son propios de la demo. Esto no es una optimización de
  espacio: es la demostración en código, no solo en prosa, de la premisa
  central de todo el proyecto — "construir el framework una vez,
  reutilizarlo en múltiples proyectos" (ver sección 1). Un bug real
  apareció aquí durante la implementación: un cálculo de niveles de
  directorio (`dirname(__DIR__, 2)` en vez de `3`) rompía el autoload por
  completo — corregido y verificado antes de dar la fase por cerrada.
- **`bin/biz`, `bin/server.php` y `resources/stubs/` sí se copian**
  (no se comparten por ruta relativa) — a diferencia de `framework/`,
  estos son parte de "cómo se trabaja en este proyecto específico", no
  del framework en sí. Confirma el criterio de la arquitectura: lo que
  es realmente framework (Fase 1-13, en `framework/`) se comparte; lo
  que es "capa de proyecto" (`bin/`, `app/`, `database/`, `routes/`,
  `resources/`, `modules/`, `config/`) se copia y adapta por proyecto.
- **Interfaz real, no solo endpoints JSON.** `resources/views/app.php`
  usa `public/css/misi.css` + `public/js/api.js` + `public/js/ui.js` de
  la Fase 13 **sin modificarlos** — login, tablas, modales con formularios
  reales (`formSubmit()`, incluida la detección automática de
  `<input type="file">` para mandar `multipart/form-data`). Es la
  primera vez en el proyecto que se ve el stack completo funcionando en
  una pantalla, no fragmentado por fase.
- **Decisiones deliberadamente distintas a las demos del proyecto padre**,
  documentadas explícitamente en `examples/demo-app/README.md`: Storage
  protegido con `auth` (son fotos de pedidos de clientes, no avatares
  públicos — a diferencia de la demo pública de la Fase 8), y
  autorización verificada a mano en el controlador
  (`Auth::can('orders.manage')`) en vez de vía middleware, consistente
  con el criterio ya establecido en `docs/authorization.md`.
- **Módulo `Reports` con lógica real**: un resumen que combina datos de
  `customers` y `orders`, no un ping — demuestra que Modules (Fase 10)
  resuelve necesidades de negocio genuinas, no solo la mecánica de
  registro de rutas/migraciones.
- Probado de punta a punta contra MariaDB real (base de datos separada
  de la del proyecto padre): CSRF, validación con email duplicado, upload
  real de imagen con verificación de contenido, protección de acceso a
  Storage con y sin sesión, cambio de estado, borrado con permiso
  verificado, y cascada de borrado vía foreign key.

## 19. Estrategia de autoloading

- PSR-4 vía Composer (`composer.json` ya configurado con `Misi\` →
  `framework/`, `App\` → `app/`).
- **Autoload de respaldo sin Composer** (`bootstrap/autoload.php`): un
  `spl_autoload_register` manual que mapea los mismos namespaces. Esto es
  intencional: permite entregar un proyecto a un hosting compartido que no
  tenga Composer/SSH disponible, sin sacrificar la posibilidad de usar
  Composer cuando sí esté disponible (el bootstrap prioriza
  `vendor/autoload.php` si existe).

## 20. Estrategia de testing

- No se escriben "cientos de tests" desde el día uno. Se prioriza que el
  código sea testeable (constructores explícitos, sin estado estático
  oculto salvo donde es intencional como `Env`).
- Cuando se agregue testing automatizado (a partir de que haya lógica no
  trivial que lo justifique, especialmente en Validation, Database
  transaccional, Router y Security), se usará **PHPUnit** por ser el
  estándar de facto en PHP, ampliamente documentado, y porque una
  desarrolladora junior/intermedia lo puede aprender rápido. Al ser una
  dependencia de **desarrollo** (`require-dev`), no afecta el despliegue en
  hosting compartido.
- Orden de prioridad para tests reales: Database (transacciones), Router
  (matching de rutas y parámetros), Validation (reglas), Auth, Storage
  (validación de uploads), Security (CSRF).

## 21. Dependencias propuestas

| Dependencia | ¿Para qué? | ¿Por qué no implementarlo propio? | Impacto despliegue | Impacto mantenimiento |
|---|---|---|---|---|
| Ninguna en `require` (producción) | — | El objetivo es cero dependencias obligatorias para que Misi corra en cualquier hosting compartido con solo PHP+MySQL | Ninguno | Ninguno |
| `phpunit/phpunit` (futuro, `require-dev`) | Testing automatizado | Reimplementar un test runner sería puro desperdicio de tiempo; PHPUnit es el estándar y no viaja a producción | Ninguno (dev only) | Bajo, muy documentado |

No se plantea ninguna otra dependencia por ahora. Cualquier propuesta futura
debe justificar explícitamente: qué problema resuelve, por qué no conviene
implementarlo a mano, impacto en despliegue e impacto en mantenimiento (regla
definida en los principios del proyecto).

## 22. Riesgos técnicos

1. **Tentación de sobre-generalizar el Router o el Query Builder.**
   Mitigación: regla de oro de abstracciones (sección 46 de los
   lineamientos del proyecto) — no abstraer sin repetición real observada
   en ≥2 proyectos.
2. **El "framework sabe de negocio" por accidente.** Mitigación: revisión
   consciente en cada PR de si algo que se está agregando a `framework/`
   en realidad pertenece a `app/`, a un módulo, o al futuro Business Core.
3. **Ausencia de contenedor de DI puede volverse incómoda** si los
   controladores empiezan a necesitar muchas dependencias (ej. varios
   servicios inyectados). Mitigación: si esto ocurre en un proyecto real,
   se agrega un contenedor **pequeño** (resolución por closures registradas,
   sin autowiring mágico por reflexión) — evaluado explícitamente, no de
   forma automática.
4. **Seguridad de uploads** es un área de alto riesgo (path traversal,
   ejecución de PHP subido). Mitigación: la Fase 8 debe implementar
   verificación de MIME real (`finfo`), lista blanca de extensiones,
   nombres generados, y documentar la configuración de servidor necesaria
   (deshabilitar ejecución de PHP en el directorio de uploads).
5. **Hosting compartido variable.** No todos los hosting compartidos
   permiten `composer install` o acceso SSH. Mitigación ya implementada:
   autoload de respaldo sin Composer (sección 14).
6. **Deriva de alcance ("scope creep") hacia un framework generalista.**
   Mitigación: cada fase del roadmap debe justificarse con una necesidad
   real de un proyecto, no con "podría servir".

## 23. Qué NO debemos implementar (por ahora)

- ORM completo (Eloquent-like).
- Contenedor de inyección de dependencias con autowiring por reflexión.
- Sistema de eventos/hooks genérico.
- Colas de trabajos (queues) / workers permanentes.
- WebSockets.
- Multi-idioma (i18n) — solo si un cliente real lo requiere.
- Cache distribuido (Redis/Memcached) — cache en filesystem si acaso.
- CLI con autogeneración de código sofisticada (scaffolding con IA, etc.)
  más allá de plantillas simples.
- Sistema de plugins de terceros / marketplace.
- Multi-tenancy a nivel de infraestructura (bases de datos separadas por
  cliente); el multi-tenant inicial es a nivel de fila (`business_id`) en
  el futuro Business Core, no en el framework.
- Frontend framework (React/Vue/Angular) como dependencia del framework.

## 24. Ejemplo de cómo una aplicación usaría Misi

```php
// routes/web.php
use App\Http\Controllers\CustomerController;

$router->get('/customers', [CustomerController::class, 'index']);
$router->post('/customers', [CustomerController::class, 'store'], ['auth', 'csrf']);
$router->get('/customers/{id}', [CustomerController::class, 'show'], ['auth']);
```

```php
// app/Http/Controllers/CustomerController.php
namespace App\Http\Controllers;

use Misi\Http\JsonResponse;
use Misi\Http\Request;

final class CustomerController
{
    public function index(Request $request): JsonResponse
    {
        $db = app()->database(); // helper futuro que expone la Application actual
        $customers = $db->select('SELECT id, name, email FROM customers WHERE business_id = ?', [
            $request->input('business_id'),
        ]);

        return JsonResponse::success($customers);
    }

    public function store(Request $request): JsonResponse
    {
        // En fases futuras: $validator->validate(...) antes de insertar
        $id = app()->database()->insert('customers', [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'business_id' => $request->input('business_id'),
        ]);

        return JsonResponse::success(['id' => $id], 'Cliente creado', 201);
    }
}
```

Esto es exactamente el objetivo: la aplicación cliente solo escribe rutas,
controladores delgados y (más adelante) servicios/repositorios propios —
nunca vuelve a escribir routing, manejo de Request/Response, conexión a
base de datos, ni el ciclo de errores.
