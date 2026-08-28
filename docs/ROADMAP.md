# ROADMAP — Misi

Este roadmap se trabaja **una fase a la vez**. Ninguna fase empieza sin que
la anterior esté cerrada y aprobada. El criterio de éxito de Misi no es
"cuántas funcionalidades tiene", sino:

> ¿Cuánto tiempo ahorra crear el segundo, tercer y cuarto proyecto sobre esta base?

Leyenda de estado: ✅ Completo · 🟡 Parcial / en progreso · ⬜ Pendiente · 🧊 Congelado (no se implementa salvo necesidad real comprobada)

**Versión actual: `1.0.0`** (Fases 1-15 completas — ver `CHANGELOG.md`).
Fase 16 en adelante (Business Core) es intencionalmente posterior a este
freeze, ver la fase correspondiente más abajo.

---

## Fase 0 — Diseño

**Objetivo:** definir arquitectura, capas, responsabilidades y límites antes
de escribir código de más.

- [x] Arquitectura de 4 niveles (Framework / Business Core / Modules / Application)
- [x] Estructura de directorios inicial
- [x] Flujo de ejecución de una request
- [x] Responsabilidades por capa
- [x] Diseño de configuración, Router, Request/Response, Database, Migrations,
      Auth, Security, Storage, Modules, CLI
- [x] Estrategia de autoloading, testing, documentación
- [x] Dependencias propuestas y justificadas
- [x] Riesgos técnicos y qué NO implementar
- [x] Ejemplo de uso de la aplicación

📄 Ver [`docs/architecture.md`](docs/architecture.md)

**Estado: ✅ Completo**

---

## Fase 1 — Bootstrap

**Objetivo:** estructura ejecutable mínima: autoload, configuración,
environment, arranque de la aplicación.

- [x] Estructura de directorios completa
- [x] `composer.json` (PSR-4: `Misi\` → `framework/`, `App\` → `app/`)
- [x] Autoload de respaldo sin Composer (`bootstrap/autoload.php`)
- [x] `Misi\Support\Env` (parser de `.env` sin dependencias)
- [x] `Misi\Support\Config` (carga `config/*.php`, acceso con dot notation)
- [x] `config/app.php`, `config/database.php`, `config/storage.php`
- [x] `.env.example`
- [x] `Misi\Core\Application` (orquestador central)
- [x] `bootstrap/app.php` + `public/index.php`
- [x] Aplicación mínima funcional (`GET /` responde HTML real)

**Estado: ✅ Completo** — probado con `php -S localhost:8000 -t public`

---

## Fase 2 — HTTP

**Objetivo:** abstracciones de Request/Response.

- [x] `Misi\Http\Request` (método, URI, query, post, headers, cookies,
      files, JSON body, soporte `_method` para PUT/PATCH/DELETE en forms)
- [x] `Misi\Http\Response` (status, headers, body, `send()`)
- [x] `Misi\Http\JsonResponse` (+ helpers `success()` / `error()` con el
      formato estándar del proyecto)
- [x] `Misi\Http\RedirectResponse`
- [x] `Request::session()` — desbloqueado por la Fase 6, delega en
      `Misi\Support\Session`
- [x] `docs/http.md`

**Estado: ✅ Completo**

---

## Fase 3 — Router

**Objetivo:** routing HTTP simple y suficiente.

- [x] `get/post/put/patch/delete`
- [x] Parámetros de ruta `{param}` y catch-all `{param*}` (agregado en
      Fase 8 porque `Storage` lo necesitaba de verdad para servir rutas
      con subdirectorios — no fue especulativo)
- [x] Middleware básico (alias → callable, pipeline tipo onion) — con
      `auth`/`guest`/`csrf` ya registrados por `Application` desde las
      Fases 6-7
- [x] Manejo de ruta no encontrada → `NotFoundException` → 404 JSON
- [ ] Grupos de rutas con prefijo (`$router->group('/api', fn($r) => ...)`) — 🧊 solo si 2+ proyectos lo piden
- [ ] Rutas nombradas + generación de URL — 🧊 congelado por ahora
- [x] `docs/routing.md`

**Estado: ✅ Completo** (los dos extras siguen 🧊 congelados a propósito —
no bloquean nada, se evalúan solo si un proyecto real los necesita)

---

## Fase 4 — Database

**Objetivo:** acceso a datos seguro y simple sobre PDO.

- [x] `Misi\Database\Database`: conexión perezosa, `query`, `select`,
      `selectOne`, `insert`, `update`, `delete`
- [x] Transacciones: `beginTransaction/commit/rollBack/transaction(callback)`
- [x] 100% prepared statements (sin concatenación de SQL)
- [ ] **Fase 4.1 — Query Builder mínimo** 🧊: NO se implementa todavía.
      Se evalúa solo si 2+ proyectos repiten el mismo patrón de armar SQL
      dinámico. Límite explícito: si empieza a necesitar joins complejos,
      eager loading o scopes → se detiene, eso no es el objetivo de Misi.
- [x] **Fase 4.2 — Migrations**: `Misi\Database\Migration` (clase base,
      `up()/down()`) y `Misi\Database\Migrator` (`run()`, `rollback()`,
      `status()`, tabla `migrations` con número de batch). Migración de
      ejemplo probada de punta a punta: `database/migrations/001_create_users_table.php`.
- [x] **Fase 4.3 — Seeders**: `Misi\Database\Seeder` (clase base) +
      `database/seeders/DatabaseSeeder.php` de ejemplo (usuario admin demo
      con contraseña hasheada, con detección de duplicados para poder
      correrse más de una vez sin error).
- [x] Runner mínimo `bin/biz` (`migrate`, `migrate:rollback`,
      `migrate:status`, `db:seed`) — **no** es el CLI `biz` completo de la
      Fase 11 (que agregará `make:*` y más), es solo lo necesario para que
      Migrations/Seeders sean utilizables ya.
- [x] `docs/database.md`

**Estado: ✅ Completo** — probado de punta a punta contra MariaDB real:
`migrate` crea la tabla, `migrate:status` refleja el estado, `db:seed`
crea el admin demo (y detecta si ya existe), `migrate:rollback` revierte
limpiamente. Ver [`docs/database.md`](docs/database.md).

---

## Fase 5 — Validation

**Objetivo:** validación de input reutilizable entre proyectos.

- [x] `Misi\Validation\Validator`
- [x] Reglas iniciales: `required, nullable, string, integer, numeric,
      boolean, email, url, min, max, in, date`
- [x] Reglas dependientes de DB: `unique, exists` (requieren `Database`)
- [x] Reglas de archivos: `file, image, mimes, max_size` (validación real
      de MIME vía `finfo`, coordina con Fase 8 — Storage para el
      almacenamiento físico del archivo, que sigue pendiente)
- [x] `ValidationException` (ya definida en Fase 1) conectada al
      `Validator` real — `Application::handleException()` ya la traduce a
      un 422 JSON con errores estructurados
- [x] Errores estructurados por campo (`{"field": ["mensaje1", "mensaje2"]}`)
- [x] Helper global `app()` (`.misi/Support/helpers.php`) — agregado
      antes de tiempo respecto al roadmap original (Fase 32/Helpers) porque
      el Router no tiene contenedor de DI: sin él, cada controlador tendría
      que reconstruir su propia conexión a `Database` a mano. Es un único
      helper, no un archivo de helpers gigante.
- [x] `docs/validation.md`

**Estado: ✅ Completo** — probado de punta a punta contra MariaDB real vía
`POST /api/validate-demo` (`app/Http/Controllers/Api/ValidationDemoController.php`):
campos requeridos, formato de email, rango numérico (`min/max`), longitud
de string (`max`), `nullable` opcional, y `unique` consultando la tabla
`users` real. Ver [`docs/validation.md`](docs/validation.md).

---

## Fase 6 — Sessions / Auth

**Objetivo:** autenticación basada en sesión PHP.

- [x] `Misi\Support\Session` (wrapper de `$_SESSION`): `get/put/has/forget/flash/regenerate`,
      más `destroy()`, `getFlash()`/`hasFlash()`, `id()`, `all()`, `clear()`
- [x] Configuración segura de cookies de sesión (`HttpOnly` siempre,
      `Secure` automático si `APP_ENV=production` o forzado por
      `SESSION_SECURE`, `SameSite` configurable) vía `config/session.php`
- [x] `Misi\Auth\Auth`: `attempt()/login()/logout()/check()/guest()/user()/id()`
- [x] `password_hash()` / `password_verify()` — nunca texto plano
- [x] Regeneración de sesión tras login (anti session fixation) — antes de
      guardar el usuario en sesión, no después
- [x] Middleware `auth` y `guest` — registrados automáticamente por
      `Application` (`registerDefaultMiddleware()`), disponibles en
      cualquier proyecto sin configuración adicional
- [x] **Fase 6.1 — Roles/Permisos**: tablas `roles`, `permissions`,
      `role_user`, `permission_role` (migración
      `002_create_roles_and_permissions.php`) + `Auth::can('recurso.accion')`.
      RBAC simple, sin jerarquía de roles ni permisos condicionales.
- [x] `docs/authentication.md`, `docs/authorization.md`
- [x] Helper global `app()` conectado a `Auth`/`Session` internamente
      (Auth usa `app()->database()`, controladores usan `Auth::` estático
      directamente sin pasar por `app()`)

**Estado: ✅ Completo** — probado de punta a punta contra MariaDB real vía
`POST /api/login`, `GET /api/me`, `POST /api/logout`
(`app/Http/Controllers/Api/AuthDemoController.php`): login con credenciales
incorrectas (401), login correcto con cookie de sesión persistente,
middleware `auth` bloqueando sin sesión (401), middleware `guest`
bloqueando con sesión activa (403), `Auth::can('users.manage')` resolviendo
`true` contra las tablas de roles/permisos reales, y logout destruyendo la
sesión (`/api/me` vuelve a dar 401 después). Ver
[`docs/authentication.md`](docs/authentication.md) y
[`docs/authorization.md`](docs/authorization.md).

---

## Fase 7 — Security

**Objetivo:** protecciones transversales.

- [x] CSRF: `Misi\Security\Csrf` + helpers `csrf_token()`, `csrf_field()`,
      `csrf_validate()`, middleware `csrf` registrado automáticamente por
      `Application`, soporte para header `X-CSRF-Token` (fetch/AJAX) y
      body `_token` (forms tradicionales)
- [x] Revisión activa de XSS (escape por defecto donde ya hay output HTML
      dinámico; se revisa de nuevo cuando llegue un motor de vistas real
      en la Fase 13)
- [x] Cookies seguras según entorno (`APP_ENV=production`) — ya
      implementado en la Fase 6 (`config/session.php`), confirmado en la
      auditoría de esta fase
- [x] Checklist de seguridad aplicado a todo el código existente hasta la
      fecha (SQL injection, XSS, CSRF, session fixation/hijacking, path
      traversal, IDOR, exposición de información, secretos expuestos)
- [x] `docs/security.md` (checklist reutilizable para futuros módulos)

**Estado: ✅ Completo** — probado de punta a punta contra MariaDB real:
`POST /api/logout` sin token (419, sesión intacta), con token incorrecto
(419, sesión intacta), con token correcto obtenido de
`GET /api/csrf-token` en la misma sesión (200, sesión cerrada). Ver
[`docs/security.md`](docs/security.md) para el checklist completo,
incluyendo lo que queda deliberadamente diferido a Storage (Fase 8) y
Hardening (Fase 15).

---

## Fase 8 — Storage

**Objetivo:** manejo seguro de archivos subidos.

- [x] `Misi\Storage\StorageInterface` (`put/putUploadedFile/get/delete/exists/url/size/mimeType`)
- [x] `Misi\Storage\LocalStorage` (única implementación)
- [x] Validación de uploads: MIME real (`finfo`, vía `Validation` Fase 5),
      extensión, tamaño máximo, nombre generado (nunca el original),
      verificación de `is_uploaded_file()` por parte de `Storage` mismo
      (defensa en profundidad, no confía ciegamente en Validation)
- [x] Prevención de path traversal (`LocalStorage::fullPath()` rechaza
      cualquier `..`) y de ejecución de PHP en `storage/uploads` (el
      directorio vive fuera de `public/`, solo accesible vía una ruta
      controlada que nunca hace `include`/`require` sobre el contenido)
- [x] Metadata de archivos en base de datos (nunca el archivo como BLOB)
      — migración de ejemplo `003_create_uploads_table.php`
- [x] `docs/storage.md`
- [x] Soporte de parámetro de ruta catch-all `{path*}` agregado al Router
      (necesario para servir archivos con subdirectorios) — necesidad
      real de esta fase, no especulativa
- 🧊 `S3Storage` / `CloudStorage`: solo si un proyecto real lo necesita
      (arquitectura ya preparada vía `StorageInterface`, no se implementa
      todavía)

**Estado: ✅ Completo** — probado de punta a punta contra MariaDB real vía
`POST /api/uploads`, `GET /storage/{path*}`, `DELETE /api/uploads/{id}`
(`app/Http/Controllers/Api/UploadDemoController.php`): subida sin login
(401), subida sin token CSRF (419), archivo falso disfrazado de imagen
rechazado por contenido real (422), imagen válida pero demasiado pesada
rechazada (422), subida válida (201) con metadata guardada en MySQL,
archivo servido con el `Content-Type` correcto, intento de path traversal
bloqueado (422, nunca llega al filesystem real), y borrado limpio
(archivo + fila de metadata). Ver [`docs/storage.md`](docs/storage.md).

---

## Fase 9 — Logging / Error Handling

**Objetivo:** trazabilidad técnica y manejo de errores consistente.

- [x] `Misi\Logging\Logger` (niveles: debug/info/warning/error/critical,
      escritura en `storage/logs/misi-YYYY-MM-DD.log`, un archivo por
      día, formato legible)
- [x] Nunca loguear contraseñas ni datos sensibles: redacción automática
      y recursiva de claves de contexto (`password`, `token`, `secret`,
      `api_key`, `csrf`, etc.) + decisión explícita de NO registrar el
      stack trace completo (puede contener argumentos sensibles de
      función). Verificado forzando un fallo real de conexión a MySQL:
      la contraseña real no aparece ni en la respuesta ni en el log.
- [x] Excepciones ya definidas en Fase 1 (`HttpException`,
      `NotFoundException`, `ValidationException`) + `StorageException`
      (Fase 8) — se agregan ahora `AuthenticationException` (401) y
      `AuthorizationException` (403), usadas por el middleware
      `auth`/`guest` de la Fase 6, y `DatabaseException` (500), usada por
      `Database::connection()` para envolver fallos de PDO con mensaje
      seguro + detalle conservado vía `getPrevious()`
- [x] Handler de errores global mejorado: `Application::handleException()`
      ahora registra en el log solo lo que representa un problema real
      del sistema (status ≥ 500 o excepción no controlada) — 404/401/403/
      419/422 son tráfico normal y no ensucian el log. En desarrollo
      (`APP_DEBUG=true`) sigue mostrando el error completo; en producción,
      mensaje genérico + registro en el log, sin stack trace expuesto ni
      en la respuesta ni en el archivo de log
- [x] `docs/logging.md`

**Estado: ✅ Completo** — probado de punta a punta: `Logger` aislado
(filtrado por nivel mínimo, redacción recursiva de `password`/
`password_confirmation`), y contra MariaDB real vía HTTP: 404 y 401 no
generan entradas de log, un fallo real de conexión (credenciales
incorrectas a propósito) genera una entrada `ERROR` con la causa completa
de PDO pero sin la contraseña, la respuesta al cliente es un 500 genérico
seguro, y tras restaurar las credenciales la operación vuelve a la
normalidad sin entradas de log adicionales. Ver
[`docs/logging.md`](docs/logging.md).

---

## Fase 10 — Modules

**Objetivo:** infraestructura para módulos de negocio reutilizables entre
proyectos (no los módulos de negocio en sí todavía).

- [x] Convención de carpeta por módulo:
      `modules/NombreModulo/{Controllers,Services,Repositories,Models,Views,routes.php,migrations,module.php}`
- [x] Mecanismo de registro/descubrimiento de módulos en `Application`
      (`discoverModules()` lee `modules/*/module.php`, `loadRoutes()`
      engancha su `routes.php`, `bin/biz` engancha sus
      `migrations/` vía `$app->modules()`)
- [x] Namespace `Modules\` (mapeado en `composer.json` y en el autoload
      de respaldo sin Composer)
- [x] `docs/modules.md`
- [x] **Sin** sistema de eventos/hooks genérico — solo si varios módulos
      reales lo requieren
- [x] Módulo de referencia funcional (`modules/Example/`), no solo
      documentación — demuestra rutas + migraciones con prefijo + uso de
      `app()->database()` desde un controlador con namespace `Modules\`
- [x] `Misi\Database\Migrator` extendido para aceptar múltiples fuentes
      (core + cada módulo), preservando el identificador sin prefijo de
      las migraciones del core ya existentes antes de esta fase

**Estado: ✅ Completo** — probado de punta a punta contra MariaDB real:
`migrate:status` muestra las migraciones del core y la del módulo
(`Example/001_....php`) correctamente prefijada, `migrate` las ejecuta
todas en un mismo batch, la ruta del módulo (`GET /modules/example/ping`)
responde y usa su propia tabla, las rutas normales de `routes/web.php`
siguen funcionando sin cambios, y `migrate:rollback` revierte limpiamente
tanto las migraciones del core como la del módulo. Ver
[`docs/modules.md`](docs/modules.md).

---

## Fase 11 — CLI (`bin/biz`)

**Objetivo:** comandos de desarrollo que ahorren trabajo repetitivo.

- [x] `migrate` / `migrate:rollback` / `migrate:status` / `db:seed` —
      ya disponibles desde la Fase 4.2/4.3 (antes vía el runner mínimo
      `bin/console.php`, ahora consolidados en `bin/biz`)
- [x] Consolidación de `bin/console.php` en el CLI definitivo `bin/biz`
      (se elimina `bin/console.php`; todas las referencias en docs/README
      actualizadas)
- [x] `php bin/biz serve` (wrapper de `php -S` que pasa `public/index.php`
      como router script — soluciona el problema real del servidor
      embebido con rutas que terminan en extensión de archivo estático,
      ver `docs/storage.md`), con `--host`/`--port` y puerto posicional
- [x] `php bin/biz help` / sin argumentos / comando desconocido → ayuda
      con la lista de comandos
- [x] `docs/cli.md`

**Estado: ✅ Completo** — probado de punta a punta: `help` (con y sin
argumentos, y ante un comando desconocido), los cuatro comandos de base
de datos contra MariaDB real (incluyendo `Example/...` del módulo de la
Fase 10), el binario ejecutado directamente vía shebang (`./bin/biz`), y
`serve` levantando el servidor y sirviendo correctamente un archivo real
de Storage (`Content-Type: image/png`) sin necesitar el truco manual de
pasar `public/index.php` a mano. Ver [`docs/cli.md`](docs/cli.md).

---

## Fase 12 — Generadores (`make:*`)

**Objetivo:** scaffolding de código repetitivo.

- [x] `php bin/biz make:controller NombreController` (soporta subcarpetas,
      ej. `Api/Ping` → `App\Http\Controllers\Api\PingController`)
- [x] `php bin/biz make:model Nombre`
- [x] `php bin/biz make:service NombreService`
- [x] `php bin/biz make:repository NombreRepository`
- [x] `php bin/biz make:module NombreModulo` (genera la estructura completa de Fase 10)
- [x] Plantillas de código simples y legibles (sin generación "mágica"):
      `resources/stubs/*.stub` con placeholders `{{clave}}` — lo que se
      genera es exactamente lo que se ve en el stub
- [x] Ninguno sobrescribe un archivo/carpeta existente (falla
      explícitamente en vez de arriesgar trabajo ya hecho)
- [x] `make:model`/`make:repository` adivinan el nombre de tabla con
      pluralización heurística en inglés (`Product` → `products`,
      `Category` → `categories`, `Business` → `businesses`), avisando el
      nombre asumido para que se ajuste a mano si no coincide

**Estado: ✅ Completo** — probado de punta a punta: los cinco generadores
producen código con sintaxis válida (`php -l` sobre cada archivo
generado), `make:controller` con y sin subcarpeta, pluralización
correcta en los tres casos de prueba, protección contra sobrescritura
verificada explícitamente (falla con exit code 1 tanto para un archivo
como para un directorio de módulo ya existente), validación de argumento
faltante, y confirmación de que un módulo recién generado (vacío) no
rompe `migrate:status` ni el resto de la aplicación. Ver
[`docs/cli.md`](docs/cli.md).

---

## Fase 13 — UI utilities

**Objetivo:** frontend base reutilizable, sin dependencia de frameworks JS.

- [x] `public/css/`: base visual simple, personalizable por variables CSS
      en `:root` (colores, tipografía, radios, sombras) por proyecto —
      vive en `public/`, no en `resources/css/` como sugería el diagrama
      original (ver justificación en `docs/frontend.md`)
- [x] `public/js/`: utilidades vanilla — `apiFetch()`, `showAlert()`,
      `confirmAction()`, `modal()`, `formSubmit()`
- [x] Wrapper de API JS: `api.get/post/put/patch/delete/upload` (JSON,
      errores, CSRF automático vía `/api/csrf-token`, estados HTTP)
- [x] Componentes HTML/CSS base: buttons, forms, tables, alerts, modals,
      navigation, pagination, cards — los 8, con referencia viva en
      `resources/views/ui-kit.php` (`GET /ui-kit`)
- [x] `docs/frontend.md`
- 🧊 Nada de React/Vue/Angular/Next.js como dependencia del framework
- [x] **Bug real descubierto y corregido**: la solución de la Fase 8/11
      para el servidor embebido ("pasar `public/index.php` como router
      script") resultó incompleta — nunca servía archivos estáticos
      reales, así que `/css/misi.css` devolvía el 404 JSON de Misi.
      Se creó `bin/server.php` (router script dedicado, exclusivo del
      servidor embebido) que resuelve ambos casos: estáticos reales
      servidos directamente, rutas dinámicas siguiendo a la app.
      `bin/biz serve` actualizado para usarlo.

**Estado: ✅ Completo** — probado de punta a punta: `api.js` ejecutado de
verdad con Node (`vm` + `fetch` nativo, no solo revisión visual) contra
un servidor Misi vivo — `api.get`, `api.post` con datos válidos, y el
caso de error 422 con `errors` poblado exactamente como los devuelve
`ValidationException`, incluyendo el flujo completo de obtención y envío
automático del token CSRF. Verificado además que, tras el fix de
`bin/server.php`, `/css/misi.css` (estático real) y `/storage/{path*}`
(dinámico, Fase 8) conviven correctamente en la misma sesión de
servidor. Ver [`docs/frontend.md`](docs/frontend.md).

---

## Fase 14 — Demo Application

**Objetivo:** aplicación de ejemplo que demuestre todo lo anterior
funcionando junto, sin ser parte del framework.

- [x] `examples/demo-app/` con: login, usuarios, CRUD, base de datos,
      validación, upload, API, permisos, un módulo de ejemplo
- [x] Explícitamente fuera de `.misi/`, `app/`, `modules/` de producción
- [x] Reutiliza el `.misi/` del proyecto padre por ruta relativa (no
      copia) — demuestra en código, no solo en documentación, la premisa
      central del proyecto: construir una vez, reutilizar en múltiples
      proyectos
- [x] Caso de uso real y concreto: taller de bordados con clientes y
      pedidos (no datos abstractos tipo "foo/bar")
- [x] Interfaz funcional de verdad (`resources/views/app.php`), usando
      `public/css/misi.css` + `public/js/api.js` + `public/js/ui.js` de
      la Fase 13 sin modificarlos — primera vez que se ve todo el stack
      funcionando junto en una pantalla, no solo en endpoints JSON
      probados con curl
- [x] Módulo `Reports` con lógica de negocio real (resumen combinando
      dos tablas), no un ping — demuestra Modules (Fase 10) resolviendo
      algo genuino
- [x] Storage protegido con `auth` (a diferencia de la demo pública del
      proyecto padre) — decisión correcta para este caso de uso, documentada
- [x] Autorización verificada a mano en el controlador
      (`Auth::can('orders.manage')`), consistente con `docs/authorization.md`
- [x] `examples/demo-app/README.md` explicando qué demuestra cada parte

**Estado: ✅ Completo** — probado de punta a punta contra MariaDB real
(base de datos separada, `bordados_demo`): migraciones y seed poblando
datos demo, login, listado de clientes/pedidos, creación con validación
(incluyendo rechazo de email duplicado), subida de imagen real con
`multipart/form-data`, imagen servida correctamente solo con sesión
activa (401 sin sesión, 200 con sesión), cambio de estado de pedido,
borrado con permiso verificado, cascada de borrado vía foreign key, y
que la imagen deja de existir tras borrar su pedido (404). 12 casos de
prueba, todos exitosos.

---

## Fase 15 — Hardening

**Objetivo:** revisión transversal antes de considerar Misi listo para
uso intensivo en proyectos de clientes.

- [x] **Auditoría de seguridad completa** (checklist de `docs/security.md`
      aplicado a todo el código):
      - [x] Búsqueda exhaustiva de SQL concatenado en todo el proyecto:
            sin hallazgos
      - [x] Cabeceras de seguridad HTTP (`X-Content-Type-Options`,
            `X-Frame-Options`, `Referrer-Policy`, `Strict-Transport-Security`
            condicional a HTTPS real — incluso detrás de un proxy TLS
            como Cloudflare/InfinityFree, vía `X-Forwarded-Proto`)
            aplicadas por defecto a toda respuesta en
            `Application::applyDefaultSecurityHeaders()`. `Content-Security-Policy`
            deliberadamente fuera (sin default seguro posible sin
            conocer el frontend de cada proyecto — ver `docs/security.md`)
      - [x] Validación de identificadores (nombre de tabla/columna) en
            `Database::insert()`/`update()`/`delete()` como defensa en
            profundidad — verificado con un intento real de inyección
            contra MariaDB
      - [x] XSS revisado en `examples/demo-app` (único HTML dinámico
            real del proyecto): sin hallazgos
      - [x] Decisión ya documentada de no implementar rate limiting de
            login ni 2FA todavía (sin necesidad real comprobada)
      - Detalle completo: sección "Auditoría Fase 15" en `docs/security.md`
- [x] **Revisión de manejo de errores en todos los módulos**: revisado
      `Application::handleException()` y cada controlador de `app/` y
      `examples/demo-app/app/` — todos usan las excepciones propias de
      Fase 9, ninguno atrapa/silencia errores ni expone detalles internos
- [x] **Revisión de rendimiento básico**:
      - [x] `role_user` no tenía índice utilizable para el filtro por
            `user_id` que hace `Auth::can()` en cada verificación de
            permiso (solo la `PRIMARY KEY` compuesta `(role_id, user_id)`,
            inútil por la regla de prefijo izquierdo). Corregido con
            migración aditiva `004_add_role_user_user_id_index.php`
            (y su equivalente en `examples/demo-app`), verificado con
            `SHOW INDEX` contra MariaDB real
      - [x] Resto de tablas (`uploads`, `customers`, `orders`) revisadas:
            sin el mismo patrón — sus filtros ya están cubiertos por
            `PRIMARY KEY` o foreign keys (indexadas automáticamente por
            InnoDB)
      - [x] Hallazgo adicional no relacionado con índices: `ext-mbstring`
            (usada por `Validator` para `mb_strlen()`) no estaba declarada
            ni verificada — sin ella, cualquier validación de string
            fallaba con un error no controlado. Corregido: declarada en
            `composer.json` y verificada en tiempo de ejecución al
            arrancar (`bootstrap/autoload.php`, proyecto raíz y
            `examples/demo-app`), con mensaje claro en vez de un 500
            críptico. Reproducido y verificado el fix contra un servidor
            real sin la extensión y luego con ella instalada.
- [x] **Verificación de compatibilidad real en un hosting compartido
      barato sin SSH — InfinityFree**:
      - [x] Auditoría de funciones no soportadas (`exec`/`shell_exec`/
            `proc_open`/`system`/`popen`): ninguna se usa en el camino
            de ejecución de producción (`passthru()` solo existe en
            `bin/biz serve`, exclusivo de desarrollo local)
      - [x] Estructura de despliegue documentada: `htdocs/` = contenido
            de `public/`, resto del framework fuera del webroot (sin
            necesitar reapuntar el document root, que InfinityFree no
            permite)
      - [x] `deploy/infinityfree/htdocs.htaccess`: front controller +
            redirect HTTPS + bloqueo de dotfiles + cabeceras de
            seguridad básicas
      - [x] `deploy/infinityfree/web-runner.php`: migraciones/seeds sin
            SSH (InfinityFree no da acceso remoto a MySQL ni SSH),
            reutilizando `Migrator`/`Seeder` ya probados, protegido por
            `DEPLOY_TOKEN` (`hash_equals`, 404 genérico si falla)
      - [x] `docs/deployment-infinityfree.md`: guía completa (carpetas,
            `.env` de producción, límites reales del plan gratuito —
            sin cron de sistema, sin `mail()`, límite de inodes/hits —
            y checklist de verificación post-despliegue)
      - [x] `.env.example` actualizado con `DEPLOY_TOKEN`
      - [ ] Verificación real contra una cuenta InfinityFree en vivo
            (lo anterior es diseño + código + documentación; falta la
            prueba de punta a punta en un hosting real antes de dar
            esta fase por completamente cerrada — igual que las demás
            fases, que se probaron contra MariaDB real, no solo en
            teoría)
- [x] Documentación completa y consistente en `docs/`: revisada la
      totalidad de `docs/*.md` contra el código actual — corregidos
      `docs/http.md` (faltaba `Request::isSecure()`/cabeceras por
      defecto), `docs/database.md` (faltaba la validación de
      identificadores), `docs/authorization.md` (faltaba el índice
      nuevo), `docs/cli.md` (faltaba el cruce con el web-runner de
      InfinityFree), `docs/architecture.md` (dependencias, riesgos
      técnicos, sección 18.1 nueva apuntando a la auditoría). Verificado
      además que no hay enlaces rotos entre docs ni docs huérfanos.
      `README.md`: corregida una omisión real (la Fase 11 — CLI no
      aparecía en "Estado actual" pese a estar completa) y agregado
      `deploy/` al árbol de estructura del proyecto.
- [x] **Congelar versión `1.0.0`**: `composer.json` (`"version": "1.0.0"`)
      y `CHANGELOG.md` creado con el historial completo 0.1.0 → 1.0.0
      (primera release etiquetada, tal como pide la sección
      "Versionado" más abajo). Etiquetar el commit correspondiente
      (`git tag v1.0.0`) queda como paso manual en el repositorio real
      — este proyecto no tiene `.git` en el entorno donde se hizo este
      trabajo.

**Nota honesta sobre el único ítem que sigue sin poder cerrarse desde
acá**: la prueba de punta a punta contra una **cuenta InfinityFree real**
no se hizo — no hay forma de crear una cuenta de hosting real desde este
entorno. Lo que sí se hizo (y es lo máximo verificable sin esa cuenta) es
simular su estructura exacta (`htdocs/` separado del resto) contra
MariaDB real, con resultado exitoso (ver sección de InfinityFree arriba).
`1.0.0` se congela con esta salvedad explícita, no oculta: el código, el
diseño y la documentación de compatibilidad están completos y probados
todo lo posible desde aquí; la verificación en un hosting real queda
como acción pendiente de quien tenga la cuenta.

**Estado: ✅ Completo** (con la salvedad de la prueba en InfinityFree
real, documentada arriba y en `docs/deployment-infinityfree.md`).

---

## Fase 16 — Business Core

**Objetivo:** capa reutilizable de funcionalidades administrativas comunes
a la mayoría de pequeños negocios, construida **sobre** el framework, nunca
mezclada con él. Vive en `business/`, namespace `Misi\Business\`.

**Primer corte (en progreso)** — basado en dos proyectos reales:
`examples/demo-app` (taller de bordados, en producción/demo) y un
segundo proyecto de tienda/retail (en planificación, con pedidos de
línea de detalle).

- [x] `Customers`: `business/Customers/CustomerRepository.php` +
      `business/migrations/001_create_customers_table.php`. Movido tal
      cual desde `examples/demo-app` — forma idéntica en ambos proyectos
      reales (nombre, email, teléfono), sin ningún campo específico de
      negocio. El caso de libro para esta capa.
- [ ] `Orders`: **deliberadamente NO generalizado todavía**, aunque
      ambos proyectos "tienen pedidos". La forma diverge demasiado para
      generalizar de forma responsable con la evidencia actual:
      - demo-app: pedido = una descripción de texto libre + imagen de
        referencia + estado con vocabulario propio del taller
        (`pendiente/en_proceso/listo/entregado`).
      - retail (planeado): pedido = líneas de detalle (producto,
        cantidad, precio por línea), total calculado, sin imagen de
        referencia, vocabulario de estado probablemente distinto
        (pago/envío en vez de manufactura).
      - Lo único genuinamente común es "un pedido pertenece a un
        cliente y tiene un estado" — generalizar solo eso ahorraría
        muy poco tiempo real, y forzar más (líneas de detalle
        opcionales, vocabulario de estado configurable) sería diseñar
        a partir de un proyecto que todavía no tiene una sola línea de
        código, no de dos implementaciones reales comparables.
      - **Se retoma cuando el proyecto retail tenga una implementación
        real** de `orders`/`order_items` — ahí sí habrá dos formas
        concretas que comparar, en vez de una real y una supuesta.
- [ ] `Users` (distinto de "clientes" del negocio) — sin segunda
      necesidad real confirmada todavía; `examples/demo-app` ya tiene
      usuarios vía Auth (Fase 6), no está claro que un "Business Core
      User" sea algo distinto de eso.
- [ ] `Businesses` (multi-tenant) — 🧊 **deliberadamente congelado**:
      ninguno de los dos proyectos reales necesita que varios negocios
      compartan un mismo despliegue (cada proyecto de cliente es su
      propio despliegue independiente, según el modelo de negocio
      descrito — ver la introducción del proyecto). Agregar
      `business_id` ahora sería diseñar para un escenario SaaS que no
      existe todavía. Se revisita si algún proyecto real lo pide.
- [x] `Products` / `Categories` — **decisión explícita del dueño del
      proyecto** (no la regla automática de "2+ proyectos"): aunque solo
      el proyecto retail (en planificación) los necesita hoy, catálogo +
      inventario + acceso admin es independiente del tipo de producto
      que se venda — a diferencia de `Modules\Ropa` (talla/color, sí
      específico), esta base la va a necesitar cualquier proyecto futuro
      que venda productos. `business/Products/{ProductRepository,CategoryRepository}.php`
      + `business/migrations/002_.../003_...`. Stock simple (un número
      por producto, sin variantes), ajuste atómico
      (`ProductRepository::adjustStock()`, previene condiciones de
      carrera y stock negativo con un único UPDATE condicionado, no
      leer-luego-escribir). Probado contra MariaDB real: CRUD, JOIN con
      categoría, ajuste válido/insuficiente, SKU duplicado.
- [ ] `Payments`, `Deliveries`, `Files`, `Reports` — sin evidencia de
      ningún proyecto real todavía.

**Nota sobre `examples/demo-app`:** su `CustomerController` sigue con
las queries propias, no fue migrado a usar
`business/Customers/CustomerRepository.php`. Es una decisión deliberada
para no arriesgar el demo ya entregado y probado — adoptar Business Core
ahí queda como mejora futura opcional, no requisito de este corte.

**Estado: 🟡 Parcial** — `Customers` completo y probado contra MariaDB
real; el resto explícitamente diferido con la razón documentada arriba,
no simplemente "no hecho".

---

## Fase 17 — Modules de negocio concretos *(post Business Core)*

Ejemplos de módulos verticales que se construirán sobre el Business Core
conforme aparezcan proyectos reales que los necesiten:

- [x] `Modules\Catalog` — catálogo + inventario + acceso admin,
      **deliberadamente genérico** (no específico de un tipo de
      producto, a diferencia de `Ropa`/`Bordados` más abajo). Construido
      con la información concreta del segundo proyecto real (tienda/
      retail): stock simple (un número por producto, sin variantes) y
      panel admin con RBAC ya existente (Fase 6). Sin migraciones
      propias — las tablas (`categories`, `products`) viven en Business
      Core (`business/migrations/`); el módulo solo agrega rutas +
      controladores + panel HTML. Ver `docs/business-core.md`.
- [ ] `Modules\Inventory` — evaluar si sigue haciendo falta como módulo
      aparte una vez que un proyecto real use `Modules\Catalog`; podría
      no ser necesario si el ajuste de stock de Catalog alcanza.
- [ ] `Modules\Ropa` — catálogo/variantes (talla, color) para tiendas de
      ropa — sigue sin un solo proyecto real de ropa, no se construye
      todavía
- [ ] `Modules\Bordados` — pedidos personalizados, tiempos de
      producción — solo `examples/demo-app` es de este tipo (1 caso, no 2)
- [ ] (otros según se detecten necesidades reales de clientes)

**Estado: 🟡 Parcial** — `Catalog` completo y probado contra MariaDB
real (CRUD de productos/categorías, ajuste atómico de stock, RBAC).
`Ropa`/`Bordados` siguen deliberadamente sin evidencia suficiente.

---

## Fase 18 — CLI profesional (`new`, `create business`, `doctor`, sintaxis agrupada)

Integración de una experiencia de CLI más completa sobre `bin/biz`
existente (auditoría previa: se descartó reconstruirlo como
Command/Kernel/Registry orientado a objetos — ver `docs/cli.md`, sección
"Por qué un solo archivo y no un Kernel/Registry" — por la misma razón
documentada desde la Fase 11: sobreingeniería para ~20 comandos).

- [x] Sintaxis agrupada (`db migrate`, `make controller Foo`,
      `create business`) como alias de los identificadores planos ya
      existentes, sin segunda implementación paralela.
- [x] `misi version` / `misi --version` — fuente única: `composer.json`.
- [x] `misi doctor` — diagnóstico de PHP, extensiones, `.env`,
      configuración, conexión a BD y permisos de `storage/`.
- [x] `misi info` — datos del proyecto sin secretos.
- [x] `misi route:list` — lee `Router::routes()` real (getter nuevo,
      sin segunda lista mantenida a mano).
- [x] `misi config:list` — lee `Config::all()` (getter nuevo), redacta
      claves que parecen sensibles (`password`/`secret`/`token`/...).
- [x] `misi db fresh` — reutiliza `Migrator::rollback()`/`run()`
      existentes (revierte lote por lote hasta vaciar, luego migra); sin
      `DROP TABLE` crudo nuevo. Pide confirmación o `--force`.
- [x] `misi serve` con alias `misi run`.
- [x] `misi new <nombre>` — copia el checkout actual (el propio proyecto
      ES la plantilla, sin instalador/paquete separado) a un directorio
      hermano, excluyendo `storage/*`, `.env`, `.git`, `examples/`,
      `AUDIT_*`, `CHANGELOG.md`.
- [x] `misi create business [tipo]` — hoy solo ofrece `catalog` (el
      único módulo de negocio real, Fase 17); ver más abajo por qué no
      hay más tipos.
- [x] Fix incidental: `Application` configuraba la sesión también en
      CLI, disparando un warning de PHP en cuanto un comando imprimía
      algo antes de instanciarla (`doctor`, `info`...). Se omite en
      `PHP_SAPI === 'cli'` — ningún comando de `bin/biz` necesita sesión.
- [ ] `make page` / `make middleware` / `make component` — Misi no tiene
      sistema de páginas-como-recurso, middleware basado en clases, ni
      sistema de componentes (solo el kit CSS/JS de la Fase 13); crear
      esas abstracciones solo para que el comando "exista" violaría la
      regla de oro. Se agregan si un proyecto real las necesita.
- [ ] `build` / `cache:clear` / `optimize` — no hay infraestructura de
      build ni de cache que limpiar/optimizar todavía (🧊 congelado, ver
      lista de abajo).
- [ ] Tipos de negocio adicionales (`restaurant`, `clothing`,
      `services`, `portfolio`...) — 🧊 **deliberadamente congelado**, ver
      lista de abajo.

**Estado: ✅ Completo** para lo que tiene base real; el resto queda
explícitamente congelado y documentado, no omitido en silencio.

---

## Qué queda deliberadamente fuera del roadmap (🧊 congelado)

Repetido aquí por visibilidad (detalle completo en `docs/architecture.md`,
sección 18):

- ORM completo tipo Eloquent
- Contenedor de DI con autowiring por reflexión
- Sistema de eventos/hooks genérico
- Colas de trabajos / workers permanentes
- WebSockets
- i18n (multi-idioma) sin necesidad real
- Cache distribuido (Redis/Memcached) — y, por lo tanto, `misi cache:clear`
- Generación de código con IA / scaffolding sofisticado
- Marketplace de plugins de terceros
- Multi-tenancy a nivel de infraestructura (DB separada por cliente)
- Cualquier framework frontend (React/Vue/Angular/Next.js) como dependencia
- Arquitectura de CLI tipo Command/Kernel/Registry (Fase 18: evaluada y
  descartada — ver `docs/cli.md`)
- Plantillas de negocio especulativas para `create business`
  (`restaurant`/`clothing`/`services`/`portfolio`/...) — se agregan solo
  cuando un proyecto real de ese tipo exista, igual que `Modules\Catalog`
  se agregó en la Fase 17 y no antes
- `build` / `optimize` — no hay assets que "bundlear" (Misi no depende
  de un build de frontend, ver Fase 13) ni configuración que
  precompilar todavía

Estas decisiones se revisan **solo** si aparece una necesidad real y
repetida en proyectos concretos — nunca de forma especulativa.

---

## Versionado

Semantic Versioning (`MAJOR.MINOR.PATCH`):

- `0.1.0` → Fases 1-3 completas (bootstrap + HTTP + Router)
- `0.2.0` → Fase 4 completa (Database + Migrations + Seeders)
- `0.3.0` → Fase 5 completa (Validation)
- `0.4.0` → Fase 6 completa (Sessions/Auth)
- `0.5.0` → Fase 7 completa (Security: CSRF)
- `0.6.0` → Fase 8 completa (Storage)
- `0.7.0` → Fase 9 completa (Logging/Errors)
- `0.8.0` → Fase 10 completa (Modules — infraestructura)
- `0.9.0` → Fase 11 completa (CLI)
- `0.10.0` → Fase 12 completa (Generadores)
- `0.11.0` → Fase 13 completa (UI utilities)
- `0.12.0` → Fase 14 completa (Demo Application)
- `1.0.0` → Fase 15 (Hardening) completa — API interna estable ←
  **estado actual**, con la salvedad documentada arriba (prueba en
  cuenta InfinityFree real pendiente, todo lo demás completo y probado)
- `1.1.0` → Fases 16-17 completas (Business Core + `Modules\Catalog`)
- `1.2.0` → Fase 18 completa (CLI profesional: `new`, `create business`,
  `doctor`, `info`, `route:list`, `config:list`, `db fresh`, sintaxis
  agrupada) ← **estado actual**

`CHANGELOG.md` creado con esta release (primera release etiquetada) —
a mantener desde ahora con cada cambio relevante.

## Convención de commits

```text
feat:     nueva funcionalidad
fix:      corrección de bug
refactor: cambio interno sin alterar comportamiento
docs:     documentación
test:     tests
security: mejoras de seguridad
```

Commits pequeños y enfocados en una sola cosa — nunca mezclar varias
funcionalidades en un mismo commit.

## Cómo se aprueba avanzar de fase

Para cada fase (ver también sección "Regla de desarrollo incremental" del
proyecto):

1. Objetivo de la fase explicado.
2. Archivos a crear/modificar mostrados antes de escribir código.
3. Implementación.
4. Verificación (lint + prueba funcional, como se hizo en Fase 1-3 con el
   servidor embebido de PHP).
5. Ejemplo de uso documentado.
6. Documentación (`docs/*.md`) actualizada.
7. Este `ROADMAP.md` actualizado (marcar checkboxes, mover estado).
8. Resumen de lo completado + pendientes.
9. Esperar aprobación antes de iniciar la siguiente fase.
