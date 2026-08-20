# ROADMAP — Misi

Este roadmap se trabaja **una fase a la vez**. Ninguna fase empieza sin que
la anterior esté cerrada y aprobada. El criterio de éxito de Misi no es
"cuántas funcionalidades tiene", sino:

> ¿Cuánto tiempo ahorra crear el segundo, tercer y cuarto proyecto sobre esta base?

Leyenda de estado: ✅ Completo · 🟡 Parcial / en progreso · ⬜ Pendiente · 🧊 Congelado (no se implementa salvo necesidad real comprobada)

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
- [x] Helper global `app()` (`framework/Support/helpers.php`) — agregado
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

- [ ] `examples/demo-app/` con: login, usuarios, CRUD, base de datos,
      validación, upload, API, permisos, un módulo de ejemplo
- [ ] Explícitamente fuera de `framework/`, `app/`, `modules/` de producción

**Estado: ⬜ Pendiente**

---

## Fase 15 — Hardening

**Objetivo:** revisión transversal antes de considerar Misi listo para
uso intensivo en proyectos de clientes.

- [ ] Auditoría de seguridad completa (checklist de `docs/security.md`
      aplicado a todo el código)
- [ ] Revisión de manejo de errores en todos los módulos
- [ ] Revisión de rendimiento básico (queries N+1 evitadas manualmente,
      uso correcto de índices en migraciones)
- [ ] Verificación de compatibilidad real en un hosting compartido barato
      (FTP + MySQL + PHP, sin SSH si es posible)
- [ ] Documentación completa y consistente en `docs/`
- [ ] Congelar versión `1.0.0` (ver Versionado más abajo)

**Estado: ⬜ Pendiente**

---

## Fase 16 — Business Core *(post-1.0, no antes)*

**Objetivo:** capa reutilizable de funcionalidades administrativas comunes
a la mayoría de pequeños negocios, construida **sobre** el framework, nunca
mezclada con él.

Candidatos (se agregan solo cuando 2+ proyectos reales los necesiten, no
antes — es el mismo principio que gobierna todo el roadmap):

- [ ] `Users` (usuarios del sistema, distinto de "clientes" del negocio)
- [ ] `Businesses` (soporte multi-negocio / multi-tenant a nivel de fila)
- [ ] `Customers`
- [ ] `Products` / `Categories`
- [ ] `Orders`
- [ ] `Payments`
- [ ] `Deliveries`
- [ ] `Files` (metadata de archivos asociados a entidades de negocio)
- [ ] `Reports` (reportes básicos reutilizables: ventas, inventario, etc.)

**Estrategia multi-tenant:** el framework solo provee las herramientas
(Database, Auth). La lógica de `business_id` y el aislamiento de datos
entre negocios pertenece enteramente al Business Core, no al framework.

**Estado: ⬜ Pendiente / futuro**

---

## Fase 17 — Modules de negocio concretos *(post Business Core)*

Ejemplos de módulos verticales que se construirán sobre el Business Core
conforme aparezcan proyectos reales que los necesiten:

- [ ] `Modules\Inventory` — inventario especializado
- [ ] `Modules\Ropa` — catálogo/variantes (talla, color) para tiendas de ropa
- [ ] `Modules\Bordados` — pedidos personalizados, tiempos de producción
- [ ] (otros según se detecten necesidades reales de clientes)

**Estado: ⬜ Pendiente / futuro** — este es el nivel donde el "ahorro de
tiempo" del framework se vuelve más visible: cada módulo nuevo reutiliza
Framework + Business Core casi por completo.

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
- Cache distribuido (Redis/Memcached)
- Generación de código con IA / scaffolding sofisticado
- Marketplace de plugins de terceros
- Multi-tenancy a nivel de infraestructura (DB separada por cliente)
- Cualquier framework frontend (React/Vue/Angular/Next.js) como dependencia

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
- `0.11.0` → Fase 13 completa (UI utilities) ← **estado actual**
- `0.12.0` → Fase 14 (Demo Application)
- `1.0.0` → Fase 15 (Hardening) completa — API interna estable

Mantener `CHANGELOG.md` a partir de la primera release etiquetada.

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
