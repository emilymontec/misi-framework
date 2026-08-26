# Misi

Base de desarrollo ligera y modular en PHP para construir rápidamente sistemas
administrativos y de gestión para pequeños negocios y emprendimientos.

Misi **no** es un framework generalista (no compite con Laravel/Symfony).
Es una herramienta interna, pensada para que una desarrolladora independiente
reutilice la misma base técnica en múltiples proyectos comerciales, reduciendo
tiempo y costo de desarrollo en cada nuevo cliente.

> Filosofía: construir una vez lo repetitivo, reutilizarlo siempre.

## Instalación y uso rápido

```bash
curl -fsSL https://raw.githubusercontent.com/TU_USUARIO/misi-framework/main/install.sh | sh

misi new tienda-maria
cd tienda-maria
misi doctor
misi db migrate
misi serve
```

Guía completa de instalación, creación de proyectos, desarrollo y
despliegue: [`INSTALL.md`](INSTALL.md). Despliegue en detalle (incluido
hosting sin SSH): [`DEPLOYMENT.md`](DEPLOYMENT.md).

---

## Estado actual

✅ **Fase 0 — Diseño**: completada (ver `docs/architecture.md`)
✅ **Fase 1 — Bootstrap**: completada
✅ **Fase 2 — HTTP** (Request/Response/JsonResponse/RedirectResponse): completada
✅ **Fase 3 — Router** (rutas, parámetros, middleware básico): completada
✅ **Fase 4 — Database**: wrapper PDO + Migrations + Seeders, probado
   de punta a punta contra MySQL/MariaDB real.
✅ **Fase 5 — Validation**: reglas required/string/email/min/max/unique/
   exists/file/image/etc., conectada a `ValidationException` (422 JSON
   automático). Ver `docs/validation.md`.
✅ **Fase 6 — Sessions/Auth**: login por sesión, `Auth::attempt/user/can`,
   middleware `auth`/`guest` automáticos, RBAC simple (roles/permisos).
   Ver `docs/authentication.md` y `docs/authorization.md`.
✅ **Fase 7 — Security**: protección CSRF (`csrf_token()`, middleware
   `csrf`, header `X-CSRF-Token`), checklist de seguridad auditado. Ver
   `docs/security.md`.
✅ **Fase 8 — Storage**: `LocalStorage` con subida segura de archivos
   (MIME real, nombre generado, bloqueo de path traversal), servidos vía
   `GET /storage/{path*}`. Ver `docs/storage.md`.
✅ **Fase 9 — Logging**: `Logger` con niveles y redacción automática de
   datos sensibles, conectado al manejo de errores (solo se registran
   fallos reales del sistema, nunca 404/401/403/422). Ver `docs/logging.md`.
✅ **Fase 10 — Modules**: infraestructura de módulos (`modules/NombreModulo/`,
   descubrimiento automático, rutas + migraciones propias). Módulo de
   referencia funcional en `modules/Example/`. Ver `docs/modules.md`.
✅ **Fase 11 — CLI** (`bin/biz`): `serve`, `migrate`/`migrate:status`/
   `migrate:rollback`, `db:seed`, `help` — sin dependencias externas
   (nada de Symfony Console). Ver `docs/cli.md`.
✅ **Fase 12 — Generadores**: `make:controller/model/service/repository/module`,
   plantillas simples en `resources/stubs/`, sin sobrescritura silenciosa.
   Ver `docs/cli.md`.
✅ **Fase 13 — UI utilities**: CSS base (`public/css/misi.css`, 8
   componentes) + JS vanilla (`api.js`, `ui.js`), demo en `GET /ui-kit`.
   Ver `docs/frontend.md`.
✅ **Fase 14 — Demo Application**: `examples/demo-app/` — un taller de
   bordados completo (login, CRUD de clientes/pedidos, upload, permisos,
   módulo propio) reutilizando el `framework/` de este proyecto por ruta
   relativa, sin copiarlo. Ver `examples/demo-app/README.md`.
✅ **Fase 15 — Hardening**: completa. Auditoría de seguridad (cabeceras
   HTTP por defecto, validación de identificadores en `Database`,
   revisión de XSS), revisión de manejo de errores, revisión de
   rendimiento (índice faltante en `role_user` corregido, dependencia
   `ext-mbstring` declarada y verificada), documentación revisada de
   punta a punta, y compatibilidad con hosting compartido sin SSH
   (InfinityFree) con herramientas de despliegue listas. Ver
   [`docs/security.md`](docs/security.md) sección "Auditoría Fase 15",
   [`DEPLOYMENT.md`](DEPLOYMENT.md)
   y [`CHANGELOG.md`](CHANGELOG.md). **Salvedad honesta**: la
   verificación en hosting sin SSH se probó simulando la estructura
   exacta de InfinityFree contra MariaDB real, pero no contra una
   cuenta InfinityFree real (no hay forma de crear una desde aquí) —
   queda como acción pendiente antes de confiar un despliegue real sin
   supervisión.
⬜ Resto de fases: ver `ROADMAP.md`.

**Versión actual: `1.1.0`** — ver [`CHANGELOG.md`](CHANGELOG.md).

🟡 **Fase 16 — Business Core**: en progreso. `Customers` y
   `Products`/`Categories` completos
   (`business/Customers/`, `business/Products/`), esta última por
   decisión explícita del dueño del proyecto (catálogo/inventario es
   independiente del tipo de producto). `Orders` y multi-tenant siguen
   deliberadamente diferidos — ver
   [`docs/business-core.md`](docs/business-core.md) y `ROADMAP.md`.

🟡 **Fase 17 — Modules de negocio concretos**: `Modules\Catalog`
   completo (panel admin de catálogo/inventario sobre Business Core,
   con RBAC). `Modules\Ropa`/`Modules\Bordados` siguen sin evidencia
   real suficiente (0 y 1 proyecto respectivamente).

Esto es una **base ejecutable**, no un mockup: el servidor de desarrollo
levanta, enruta y responde JSON/HTML reales (ver sección "Probarlo ahora").

---

## Requisitos

- PHP 8.1 o superior
- MySQL (solo necesario a partir de que uses `Database`; el bootstrap y el
  router funcionan sin base de datos configurada)
- Sin dependencias obligatorias de Composer. `composer.json` está listo por
  si más adelante agregas alguna librería puntual, pero el autoload funciona
  también sin `composer install` (ver `bootstrap/autoload.php`).

## Instalación

```bash
cp .env.example .env
# Edita .env con tus credenciales de MySQL si vas a usar la base de datos

# Opcional, solo si agregas dependencias de Composer en el futuro:
composer install
```

## Probarlo ahora

```bash
php bin/biz serve
```

Internamente es un `php -S` con `bin/server.php` como router script
(no `public/index.php` directamente) — resuelve dos problemas reales del
servidor embebido de PHP a la vez: rutas dinámicas como
`/storage/{path*}` (Fase 8) y archivos estáticos reales como
`/css/misi.css` (Fase 13) sirviéndose correctamente al mismo tiempo.
Detalle completo en [`docs/frontend.md`](docs/frontend.md). En un
hosting real con Apache/Nginx esto no aplica: las reglas de rewrite ya
mandan todo a `index.php` sin necesitar nada de esto. (Equivalente
manual, por si prefieres no usar el CLI:
`php -S localhost:8000 -t public bin/server.php`.)

Por defecto escucha en `127.0.0.1:8000`; para cambiarlo:

```bash
php bin/biz serve 8080
php bin/biz serve --host=0.0.0.0 --port=8080
```

Abre `http://localhost:8000` y deberías ver la página de bienvenida.
Rutas de ejemplo incluidas en `routes/web.php`:

| Método | Ruta            | Qué demuestra                          |
|--------|-----------------|-----------------------------------------|
| GET    | `/`             | Respuesta HTML simple                   |
| GET    | `/saludo/{name}`| Parámetros de ruta                      |
| GET    | `/api/ping`     | `JsonResponse` con el formato estándar  |

Una ruta inexistente devuelve automáticamente un 404 en formato JSON
estándar de Misi (manejo centralizado de errores en `Application`).

## CLI (`bin/biz`)

```bash
php bin/biz help
```

| Comando | Qué hace |
|---|---|
| `serve` | Levanta el servidor de desarrollo (ver arriba) |
| `migrate` | Ejecuta migraciones pendientes (core + módulos) |
| `migrate:status` | Muestra qué corrió y qué falta, con su lote |
| `migrate:rollback` | Revierte el último lote completo |
| `db:seed` | Ejecuta `database/seeders/DatabaseSeeder.php` |
| `make:controller Nombre` | Genera `app/Http/Controllers/{Nombre}Controller.php` (soporta `Api/Nombre`) |
| `make:model Nombre` | Genera `app/Models/{Nombre}.php` |
| `make:service Nombre` | Genera `app/Services/{Nombre}Service.php` |
| `make:repository Nombre` | Genera `app/Repositories/{Nombre}Repository.php` |
| `make:module Nombre` | Genera `modules/{Nombre}/` completo (ver `docs/modules.md`) |

Sin dependencias externas (nada de Symfony Console): es un único script
PHP que despacha por `match($comando)`. Los generadores usan plantillas
simples en `resources/stubs/*.stub` — ninguno sobrescribe un archivo o
carpeta que ya existe. Detalle completo en [`docs/cli.md`](docs/cli.md).

## Migraciones y seeders

Con las credenciales de MySQL configuradas en `.env`:

```bash
php bin/biz migrate            # ejecuta migraciones pendientes
php bin/biz migrate:status      # muestra qué corrió y qué falta
php bin/biz migrate:rollback    # revierte el último lote
php bin/biz db:seed             # ejecuta database/seeders/DatabaseSeeder.php
```

`bin/biz` es el CLI de Misi (Fase 11) — ver la sección "CLI" más abajo
para el resto de comandos disponibles (`serve`, `help`). Ver
`database/migrations/001_create_users_table.php` y
`database/seeders/DatabaseSeeder.php` como ejemplos de referencia.

## Validation

```bash
curl -X POST http://localhost:8000/api/validate-demo \
  -H "Content-Type: application/json" \
  -d '{"name":"Ana Perez","email":"ana@example.com","age":30}'
```

Ver `database/migrations/001_create_users_table.php` + `php bin/biz
migrate` primero (la ruta demo usa `unique:users,email`). Detalle completo
en [`docs/validation.md`](docs/validation.md).

## Autenticación (Auth) y CSRF

```bash
# 1. login (guarda la cookie de sesión en cookies.txt)
curl -c cookies.txt -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@misi.test","password":"changeme"}'

# 2. ruta protegida, usando la misma cookie
curl -b cookies.txt http://localhost:8000/api/me

# 3. logout muta estado -> requiere token CSRF además de la cookie
TOKEN=$(curl -s -b cookies.txt -c cookies.txt http://localhost:8000/api/csrf-token | python3 -c "import sys,json;print(json.load(sys.stdin)['data']['token'])")
curl -b cookies.txt -c cookies.txt -X POST http://localhost:8000/api/logout \
  -H "X-CSRF-Token: $TOKEN"
```

Requiere haber corrido `migrate` y `db:seed` primero (crea
`admin@misi.test` / `changeme` con rol `admin` y permiso
`users.manage`). Detalle completo en
[`docs/authentication.md`](docs/authentication.md),
[`docs/authorization.md`](docs/authorization.md) y
[`docs/security.md`](docs/security.md).

## Storage (subida de archivos)

```bash
# Reutiliza $TOKEN y cookies.txt de la sección anterior (requiere login)
curl -b cookies.txt -X POST http://localhost:8000/api/uploads \
  -H "X-CSRF-Token: $TOKEN" \
  -F "file=@/ruta/a/una/imagen.png"

# la respuesta trae "path" y "url" -> servir el archivo:
curl http://localhost:8000/storage/avatars/<nombre-generado>.png
```

Valida imagen real (no solo extensión), tipo MIME permitido y tamaño
máximo antes de guardar. El nombre de archivo se genera siempre — nunca
se usa el original. Detalle completo, incluyendo cómo proteger archivos
privados con el middleware `auth`, en
[`docs/storage.md`](docs/storage.md).

## Modules

```bash
curl http://localhost:8000/modules/example/ping
```

`modules/Example/` es un módulo real y funcional (rutas propias +
migración propia con prefijo `Example/` + su propia tabla), pensado para
copiarse como punto de partida al crear tu primer módulo de negocio.
Requiere `php bin/biz migrate` primero. Detalle completo en
[`docs/modules.md`](docs/modules.md).

## UI utilities

```bash
php bin/biz serve
# abre http://localhost:8000/ui-kit
```

CSS base personalizable por variables (`public/css/misi.css`, 8
componentes: buttons, forms, tables, alerts, modals, navigation,
pagination, cards) + JS vanilla sin dependencias
(`public/js/api.js` con CSRF automático, `public/js/ui.js` con
`showAlert/confirmAction/modal/formSubmit`). Sin ningún framework
frontend. Detalle completo en [`docs/frontend.md`](docs/frontend.md).

## Demo Application

```bash
cd examples/demo-app
cp .env.example .env   # tu propia base de datos, separada de la de arriba
php bin/biz migrate
php bin/biz db:seed
php bin/biz serve
# abre http://localhost:8000 — login: staff@bordados.test / changeme
```

Un taller de bordados completo: login, CRUD de clientes y pedidos, subida
de imagen de referencia, permisos, y un módulo de reportes propio — todo
el stack funcionando junto, con interfaz real (no solo JSON). Reutiliza
el `framework/` de este mismo proyecto por ruta relativa, sin copiarlo:
es la prueba en código de que un segundo proyecto no repite el
framework, solo su propia capa de negocio. Detalle completo en
[`examples/demo-app/README.md`](examples/demo-app/README.md).

## Estructura del proyecto

```text
misi/
├── app/                    # Tu aplicación (controladores, etc.)
│   └── Http/Controllers/
├── bin/
│   ├── biz                  # CLI de Misi: serve, migrate*, db:seed, make:*
│   └── server.php            # Router script exclusivo del servidor embebido (ver docs/frontend.md)
├── bootstrap/               # Autoload + creación de Application
├── config/                  # app.php, database.php, storage.php, session.php, logging.php
├── database/
│   ├── migrations/          # users, roles/permissions, uploads (ejemplos)
│   └── seeders/             # DatabaseSeeder.php (ejemplo)
├── deploy/
│   └── infinityfree/        # Material de despliegue para hosting sin SSH (ver DEPLOYMENT.md)
├── docs/                    # Documentación técnica
├── framework/                # El framework Misi en sí (namespace Misi\)
│   ├── Core/                 # Application (orquestador)
│   ├── Database/             # Database, Migration, Migrator, Seeder
│   ├── Http/                 # Request/Response/JsonResponse/RedirectResponse
│   ├── Routing/               # Router + Route
│   ├── Auth/                  # Auth (attempt, login, logout, can)
│   ├── Security/               # Csrf (token, field, validate)
│   ├── Validation/            # Validator (required, email, unique, etc.)
│   ├── Storage/                # StorageInterface, LocalStorage
│   ├── Logging/                # Logger (niveles, redacción automática)
│   ├── Exceptions/            # HttpException, NotFoundException, ValidationException...
│   └── Support/               # Env, Config, Session
├── examples/
│   └── demo-app/              # App de ejemplo completa (Fase 14) — reutiliza framework/, ver su README
├── modules/                  # Módulos (ver modules/Example/ como referencia)
├── public/                    # Document root
│   ├── index.php               # Front controller (idéntico en producción)
│   ├── css/misi.css            # UI base (Fase 13)
│   └── js/api.js, ui.js          # Cliente API + utilidades UI (Fase 13)
├── resources/
│   ├── views/                   # ui-kit.php (demo de componentes)
│   └── stubs/                    # Plantillas de los generadores (make:*)
├── routes/
│   └── web.php                # Definición de rutas
├── storage/
│   ├── logs/, cache/, uploads/ # (uploads listo estructuralmente, Fase 8)
├── tests/                     # (Fase 0 estrategia definida, tests reales por fase)
├── .env.example
├── composer.json
└── ROADMAP.md
```

## Filosofía de trabajo (avance progresivo)

Misi se construye **una fase a la vez**, siguiendo el roadmap, y **solo
cuando un proyecto real lo necesita**. No se agregan abstracciones "por si
acaso". Cada nueva funcionalidad debe:

1. Resolver una necesidad real detectada en un proyecto.
2. Generalizarse solo lo necesario (no más).
3. Documentarse en `docs/`.
4. Quedar reflejada en `ROADMAP.md` y `CHANGELOG.md`.

Regla de oro: si algo puede resolverse simple y mantenible, se elige lo
simple — incluso si "se ve menos profesional" que una abstracción compleja.

## Namespaces

| Namespace  | Ubicación     | Contenido                                  |
|------------|---------------|---------------------------------------------|
| `Misi\`   | `framework/`  | El framework técnico (no conoce negocio)   |
| `App\`     | `app/`        | Tu aplicación concreta para un cliente      |
| `Modules\` | `modules/`    | Módulos de negocio reutilizables entre proyectos |

## Licencia

Uso interno / propietario.
