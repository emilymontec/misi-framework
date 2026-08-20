# CLI (`bin/biz`)

Misi trae un único script de línea de comandos, `bin/biz`, sin ninguna
dependencia externa (nada de Symfony Console). El parseo de `$argv` y las
respuestas de texto plano son simples de resolver a mano — una
dependencia de Composer aquí solo tendría sentido como `require-dev` (no
viaja a producción), y no se justifica todavía para la cantidad de
comandos que existen.

```bash
php bin/biz help
```

## Comandos disponibles

| Comando | Qué hace |
|---|---|
| `serve` | Levanta el servidor de desarrollo embebido de PHP |
| `migrate` | Ejecuta las migraciones pendientes (core + módulos, ver `docs/modules.md`) |
| `migrate:status` | Muestra qué migración corrió y cuál falta, con su lote |
| `migrate:rollback` | Revierte el último lote completo de migraciones |
| `db:seed` | Ejecuta `database/seeders/DatabaseSeeder.php` |
| `help` (o sin argumentos) | Muestra la ayuda |

## `serve`

```bash
php bin/biz serve                          # 127.0.0.1:8000
php bin/biz serve 8080                     # 127.0.0.1:8080
php bin/biz serve --host=0.0.0.0 --port=8080
```

Es un wrapper de `php -S`, pero con una diferencia importante: usa
`bin/server.php` como router script (no `public/index.php` directamente).
Sin un router script, el servidor embebido de PHP intenta servir como
archivo estático cualquier URL con una extensión conocida (`.png`,
`.css`...) y devuelve su propio 404 antes de llegar a Misi — un problema
real que afecta, por ejemplo, a las rutas de Storage
(`/storage/avatars/foo.png`, ver `docs/storage.md`). Y usar
`public/index.php` directamente como router script trae un problema
distinto: arranca la app incondicionalmente y nunca sirve un archivo
real, así que `/css/misi.css` (Fase 13) terminaría devolviendo el 404
JSON de Misi en vez del CSS real. `bin/server.php` resuelve ambos casos
a la vez — ver `docs/frontend.md` para el detalle completo. En
Apache/Nginx esto no aplica: las reglas de rewrite ya mandan todo a
`index.php` sin necesitar nada de esto.

## `migrate` / `migrate:status` / `migrate:rollback`

Ver `docs/database.md` para el formato de las migraciones y
`docs/modules.md` para cómo se combinan las del core con las de cada
módulo. Requieren `.env` configurado con credenciales de MySQL válidas.

## `db:seed`

Ejecuta `database/seeders/DatabaseSeeder.php`. Si tu proyecto tiene
varios seeders, ese archivo es el punto de entrada que los orquesta — no
hay (todavía) un mecanismo para correr un seeder individual por nombre;
se evalúa si algún proyecto real lo necesita.

## Generadores (`make:*`)

Ninguno necesita `.env` configurado ni conexión a base de datos — solo
escriben archivos a partir de plantillas en `resources/stubs/`. Ninguno
sobrescribe un archivo existente: si el destino ya existe, el comando
falla explícitamente en vez de arriesgarse a borrar trabajo ya hecho.

```bash
php bin/biz make:controller Customer          # app/Http/Controllers/CustomerController.php
php bin/biz make:controller Api/Ping          # app/Http/Controllers/Api/PingController.php
php bin/biz make:model Product                # app/Models/Product.php
php bin/biz make:service OrderProcessing      # app/Services/OrderProcessingService.php
php bin/biz make:repository Product           # app/Repositories/ProductRepository.php
php bin/biz make:module Inventory             # modules/Inventory/ completo (ver docs/modules.md)
```

**Reglas de nombre:**

- `make:controller` acepta subcarpetas con `/` (`Api/Ping` → namespace
  `App\Http\Controllers\Api`, clase `PingController`) y agrega el sufijo
  `Controller` si falta.
- `make:service` agrega el sufijo `Service` si falta.
- `make:repository` agrega el sufijo `Repository` si falta, y adivina el
  nombre de tabla (pluralización heurística en inglés: `Product` →
  `products`, `Category` → `categories`, `Business` → `businesses`). El
  comando avisa el nombre asumido — ajústalo en el archivo si tu
  migración usa otro.
- `make:model` no agrega sufijo (el nombre tal cual, ej. `Product`), y
  también adivina el nombre de tabla igual que `make:repository`.
- `make:module` genera la estructura completa
  (`Controllers/Services/Repositories/Models/Views/migrations/` +
  `module.php` + `routes.php`), vacía y lista para empezar — sin
  controlador ni migración de ejemplo dentro (para eso está
  `modules/Example/`, ya incluido en el proyecto como referencia).

**Por qué son plantillas de texto simples:** el roadmap pide
explícitamente "código limpio y entendible" — lo que ves en
`resources/stubs/*.stub` es exactamente lo que se genera, con
`{{placeholders}}` reemplazados. Nada de construcción programática de
AST ni generación "mágica".

## Por qué no hay comandos `make:*` más allá de estos

Los cinco generadores cubren la convención de capas de Misi (Controller
→ Service → Repository → Model, más Module). Comandos adicionales
(`make:middleware`, `make:migration`, `make:seeder`) se agregan solo si
copiar un archivo de ejemplo a mano empieza a doler de verdad en varios
proyectos — no de forma especulativa.

## Cómo se agrega un comando nuevo

`bin/biz` despacha con un `match($comando)` simple. Agregar un comando es
añadir un caso al `match` y una función `runNombreComando()` — sin
registrar clases, sin autodescubrimiento, sin convención de directorios.
Es deliberadamente así de directo: para el número de comandos que un
proyecto de este tamaño necesita, cualquier capa adicional sería
sobreingeniería.
