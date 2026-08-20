# Modules

Un módulo es una carpeta autocontenida en `modules/` que agrega rutas y/o
tablas propias a la aplicación, sin que `Application` necesite saber de
antemano que existe. Desde la Fase 10, la infraestructura para esto ya
está construida — lo que **no** existe todavía son módulos de negocio
reales (eso llega en fases posteriores, cuando un proyecto real lo pida).

## Estructura de un módulo

```text
modules/NombreModulo/
├── Controllers/
├── Services/
├── Repositories/
├── Models/
├── Views/
├── migrations/
├── routes.php
└── module.php
```

Solo `module.php` es obligatorio. El resto de carpetas son la convención
recomendada (calcada de `app/`), pero un módulo puede no usarlas todas —
por ejemplo, `modules/Example/` (incluido como referencia viva) solo usa
`Controllers/` y `migrations/`.

## `module.php`: el contrato mínimo

```php
<?php

declare(strict_types=1);

return [
    'name' => 'Inventory',
    'routes' => __DIR__ . '/routes.php',       // o null si no expone rutas
    'migrations' => __DIR__ . '/migrations',    // o null si no tiene tablas propias
];
```

`Application` escanea `modules/*/module.php` al arrancar (`discoverModules()`)
y no exige nada más que esas tres claves. `name` debe ser único entre
módulos — se usa como prefijo de sus migraciones en la tabla `migrations`
(`"Inventory/001_....php"`), para que dos módulos puedan numerar sus
migraciones igual sin chocar entre sí.

## Rutas de un módulo

`routes.php` recibe `$router` exactamente igual que `routes/web.php` —
mismo objeto, misma API:

```php
<?php
// modules/Inventory/routes.php

use Modules\Inventory\Controllers\ProductController;

/** @var \Misi\Routing\Router $router */

$router->get('/inventory/products', [ProductController::class, 'index'], ['auth']);
```

`Application::loadRoutes()` carga primero `routes/web.php` y después el
`routes.php` de cada módulo, en el orden en que fueron descubiertos
(alfabético por nombre de carpeta).

## Namespace y autoloading

Los controladores/servicios/modelos de un módulo viven bajo `Modules\`:

```php
namespace Modules\Inventory\Controllers;
```

Ya está mapeado tanto en `composer.json` (`"Modules\\": "modules/"`) como
en el autoload de respaldo sin Composer (`bootstrap/autoload.php`) — no
hace falta configurar nada más para que `Modules\Inventory\Controllers\ProductController`
resuelva a `modules/Inventory/Controllers/ProductController.php`.

## Migraciones de un módulo

Misma convención que `database/migrations/` (archivos numerados,
`Migration` con `up()`/`down()` — ver `docs/database.md`). `bin/biz`
ya las recorre automáticamente junto con las del core:

```bash
php bin/biz migrate         # corre core + todos los módulos
php bin/biz migrate:status   # muestra el identificador con prefijo, ej. "Inventory/001_....php"
php bin/biz migrate:rollback # revierte el ultimo batch, sin importar de que fuente venga cada migracion
```

Un detalle de compatibilidad: las migraciones del **core**
(`database/migrations/`) mantienen su identificador sin prefijo
(`"001_create_users_table.php"`, no `"core/001_..."`) — así cualquier
proyecto que ya tenía migraciones corridas antes de la Fase 10 no
necesita re-registrarlas.

## Usar Database/Validator/Storage/Auth desde un módulo

Exactamente igual que desde `app/` — el helper `app()` no distingue de
dónde lo llamas:

```php
namespace Modules\Inventory\Controllers;

use Misi\Http\JsonResponse;
use Misi\Http\Request;

final class ProductController
{
    public function index(Request $request): JsonResponse
    {
        $products = app()->database()->select('SELECT * FROM products');
        return JsonResponse::success($products);
    }
}
```

## Cómo crear tu primer módulo real

```bash
php bin/biz make:module Inventory
```

Genera la estructura completa (`module.php`, `routes.php`, y las
carpetas `Controllers/Services/Repositories/Models/Views/migrations/`
vacías) — ver `docs/cli.md` para el detalle del generador. A partir de
ahí:

1. Los generadores `make:controller`/`make:service`/`make:repository`/`make:model`
   siempre apuntan a `app/`, no a `modules/NombreModulo/` — dentro de un
   módulo, esos archivos se escriben a mano siguiendo el mismo patrón
   (copia la forma de `modules/Example/Controllers/PingController.php`
   como referencia).
2. Descomenta y ajusta la ruta de ejemplo en `routes.php`.
3. Escribe tu migración en `migrations/001_....php` (mismo formato que
   `database/migrations/`, ver `docs/database.md`).
4. Corre `php bin/biz migrate` — tu migración aparecerá con el prefijo
   de tu módulo.

Alternativa manual: copiar `modules/Example/` completo y renombrarlo
sigue funcionando igual de bien si prefieres partir de algo con
contenido de ejemplo en vez de carpetas vacías.

## Qué NO hace el sistema de Modules (a propósito)

- **Sin sistema de eventos/hooks.** Un módulo no puede "escuchar" cuando
  otro módulo hace algo. Si varios módulos reales necesitan comunicarse,
  se evalúa entonces — no antes (regla de oro de abstracciones, ver
  `docs/architecture.md`).
- **Sin activar/desactivar módulos en runtime.** Un módulo existe si su
  carpeta existe en `modules/`; quitarlo es borrar la carpeta (y correr
  `migrate:rollback` antes, si tenía tablas propias).
- **Sin dependencias entre módulos declaradas** (`"Inventory" depende de
  "Suppliers"`). Se ordenan alfabéticamente por nombre de carpeta; si el
  orden importa para las migraciones, un guion bajo numérico en el
  nombre de carpeta (`01-Suppliers/`, `02-Inventory/`) resuelve el caso
  simple sin que el framework necesite un resolutor de dependencias.
- **Sin Business Core todavía.** Los módulos de negocio reales (Inventory,
  Orders, etc.) llegan cuando un proyecto concreto los necesite (ver
  ROADMAP Fase 16-17) — esta fase solo construyó la infraestructura.
