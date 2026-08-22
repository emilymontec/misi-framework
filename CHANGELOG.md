# Changelog

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/).
Versionado según [Semantic Versioning](https://semver.org/lang/es/) — ver
`ROADMAP.md`, sección "Versionado", para el mapeo completo fase → versión.

## [1.0.0] — Fase 15 (Hardening)

Primera versión con API interna considerada estable. Cierra las 15 fases
planificadas originalmente para el framework en sí (Business Core y
módulos de negocio concretos son fases posteriores, deliberadamente
fuera de este release — ver `ROADMAP.md`, Fase 16 en adelante).

### Agregado

- Cabeceras de seguridad HTTP por defecto en toda respuesta
  (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`,
  `Strict-Transport-Security` condicional a HTTPS real, incluso detrás de
  un proxy TLS como Cloudflare/InfinityFree vía `X-Forwarded-Proto`).
- Validación de identificadores (nombre de tabla/columna) en
  `Database::insert()`/`update()`/`delete()`, como defensa en profundidad
  contra la construcción accidental de `$data` a partir de input no
  filtrado.
- `Request::isSecure()` y `Response::hasHeader()`.
- Migración `004_add_role_user_user_id_index.php`: índice faltante en
  `role_user.user_id`, columna que `Auth::can()` filtra en cada
  verificación de permiso.
- Verificación explícita de la extensión `ext-mbstring` al arrancar
  (`bootstrap/autoload.php`), con mensaje claro en vez de un error
  críptico no controlado. Declarada también en `composer.json`.
- `deploy/infinityfree/`: `.htaccess` de producción (front controller +
  redirect HTTPS + bloqueo de dotfiles) y `web-runner.php` (migraciones/
  seeds vía HTTP, protegido por token, para hosting sin SSH).
- `docs/deployment-infinityfree.md`: guía completa de despliegue en
  hosting compartido sin SSH (InfinityFree y equivalentes).

### Corregido

- `README.md` no listaba la Fase 11 (CLI) en "Estado actual" pese a estar
  completa.
- Documentación (`docs/http.md`, `docs/database.md`,
  `docs/authorization.md`, `docs/cli.md`, `docs/architecture.md`)
  actualizada para reflejar los cambios de esta fase.

### Seguridad

- Auditoría completa contra `docs/security.md`: búsqueda exhaustiva de
  SQL concatenado (sin hallazgos), revisión de XSS en
  `examples/demo-app` (sin hallazgos), auditoría de funciones de shell
  no soportadas en hosting compartido (`exec`/`shell_exec`/`proc_open`/
  `system`/`popen`/`passthru` — única ocurrencia en `bin/biz serve`,
  exclusivo de desarrollo local).

## [0.12.0] — Fase 14 (Demo Application)

- `examples/demo-app/`: aplicación completa de referencia (taller de
  bordados) — login, CRUD de clientes/pedidos, upload de imagen
  protegido con sesión, permisos, módulo `Reports` propio. Reutiliza
  `framework/` del proyecto padre por ruta relativa, sin copiarlo.

## [0.11.0] — Fase 13 (UI utilities)

- `public/css/misi.css`: base visual personalizable por variables CSS,
  8 componentes (buttons, forms, tables, alerts, modals, navigation,
  pagination, cards).
- `public/js/api.js` (wrapper fetch con CSRF automático) y
  `public/js/ui.js` (`showAlert`, `confirmAction`, `modal`, `formSubmit`).
- `resources/views/ui-kit.php` (`GET /ui-kit`) como referencia viva.
- `bin/server.php`: router script dedicado para el servidor embebido de
  PHP — corrige que `bin/biz serve` no servía archivos estáticos reales.

## [0.10.0] — Fase 12 (Generadores)

- `make:controller` / `make:model` / `make:service` / `make:repository` /
  `make:module`, plantillas de texto simples en `resources/stubs/*.stub`,
  sin sobrescritura silenciosa de archivos existentes.

## [0.9.0] — Fase 11 (CLI)

- `bin/biz`: `serve`, `migrate`, `migrate:status`, `migrate:rollback`,
  `db:seed`, `help` — consolidado sin dependencias externas.

## [0.8.0] — Fase 10 (Modules — infraestructura)

- Convención de carpeta por módulo (`modules/NombreModulo/`),
  descubrimiento automático (`Application::discoverModules()`), rutas y
  migraciones propias por módulo. Módulo de referencia:
  `modules/Example/`.

## [0.7.0] — Fase 9 (Logging / Error Handling)

- `Misi\Logging\Logger`: niveles, un archivo por día, redacción
  automática y recursiva de claves sensibles del contexto.
- `AuthenticationException`, `AuthorizationException`,
  `DatabaseException`. `Application::handleException()` centralizado:
  solo registra en log lo que representa un problema real (≥500).

## [0.6.0] — Fase 8 (Storage)

- `StorageInterface` / `LocalStorage`: subida segura (MIME real,
  extensión, tamaño, nombre generado), prevención de path traversal,
  metadata en base de datos (nunca el archivo como BLOB).
- Parámetro de ruta catch-all `{path*}` agregado al Router (necesidad
  real de esta fase).

## [0.5.0] — Fase 7 (Security)

- `Misi\Security\Csrf` + `csrf_token()`/`csrf_field()`/`csrf_validate()`,
  middleware `csrf` automático, soporte `X-CSRF-Token` para AJAX/fetch.
- Primer checklist de seguridad completo (`docs/security.md`).

## [0.4.0] — Fase 6 (Sessions / Auth)

- `Misi\Support\Session`, cookies seguras según entorno.
- `Misi\Auth\Auth`: `attempt/login/logout/check/guest/user/id`,
  regeneración de sesión anti-fixation.
- RBAC simple: `roles`, `permissions`, `role_user`, `permission_role`,
  `Auth::can('recurso.accion')`.

## [0.3.0] — Fase 5 (Validation)

- `Misi\Validation\Validator`: `required`, `string`, `email`, `min`,
  `max`, `in`, `date`, `unique`, `exists`, `file`, `image`, `mimes`,
  `max_size`, entre otras.
- Helper global `app()`.

## [0.2.0] — Fase 4 (Database)

- `Misi\Database\Database`: wrapper PDO, 100% prepared statements,
  transacciones.
- `Migration` / `Migrator` (con tracking de batch) y `Seeder`.

## [0.1.0] — Fases 1-3 (Bootstrap + HTTP + Router)

- Estructura de directorios, autoload dual (Composer PSR-4 + fallback
  manual sin Composer), `Env`, `Config`, `Application`.
- `Request` / `Response` / `JsonResponse` / `RedirectResponse`.
- Router con parámetros de ruta y middleware básico tipo pipeline.
