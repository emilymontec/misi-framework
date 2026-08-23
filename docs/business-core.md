# Business Core

Capa de funcionalidades de negocio reutilizables, construida **sobre**
el framework, nunca mezclada con él. Vive en `business/`, namespace
`Misi\Business\`, al mismo nivel que `framework/`, `app/` y `modules/`.

## Regla de esta capa

El framework (`framework/`) no sabe qué es un cliente ni un pedido —
esa regla no cambia con Business Core. `business/` sí puede depender de
`framework/` (usa `Database`, `Validator`, las excepciones de Fase 9),
nunca al revés, y **nunca se registra en `Application`** (no existe
`app()->customers()`) — un proyecto instancia las clases de
`business/` directamente en su propio código, igual que instanciaría
cualquier otra clase suya.

## Por qué esto existe (y por qué no existía antes)

Cada entidad que entra acá tiene que estar respaldada por **2+
proyectos reales** que la necesiten con la misma forma — el mismo
principio de "regla de oro de abstracciones" que gobierna el resto del
framework, aplicado a este nivel. Generalizar a partir de un solo
proyecto es adivinar; esta capa existe para ahorrar tiempo real, no
para anticipar necesidades hipotéticas.

## Qué hay hoy: `Customers`

```php
use Misi\Business\Customers\CustomerRepository;

$customers = new CustomerRepository(app()->database());

$customers->all();
$customers->find(3);
$customers->findOrFail(3);          // lanza NotFoundException si no existe

$data = app()->validator()->validate(
    $request->all(),
    $customers->rulesForCreate()
);
$id = $customers->create($data);

$data = app()->validator()->validate(
    $request->all(),
    $customers->rulesForUpdate($id)
);
$customers->update($id, $data);

$customers->delete($id);
```

Migración: `business/migrations/001_create_customers_table.php` — se
descubre sola con `php bin/biz migrate` si la carpeta `business/`
existe en el proyecto (ver "Cómo se descubre" más abajo). Aparece en
`migrate:status` bajo el label `Business`, igual que un módulo.

Es exactamente el mismo código y la misma tabla que ya usaba
`examples/demo-app/app/Http/Controllers/CustomerController.php` —
movidos acá porque un segundo proyecto real (tienda/retail, en
planificación) necesita lo mismo, con la misma forma. `demo-app` **no**
fue migrado a consumir esta clase (ver más abajo) — es la misma lógica
en dos lugares por ahora, a propósito.

### Extender las reglas de validación

Si un proyecto necesita un campo propio en `customers` (poco común,
pero puede pasar), no hace falta reescribir las reglas — se fusionan:

```php
$rules = [
    ...$customers->rulesForCreate(),
    'tax_id' => ['nullable', 'string', 'max:20'],
];
```

El campo extra necesita su propia migración en `database/migrations/`
del proyecto (ej. `ALTER TABLE customers ADD COLUMN tax_id ...`),
aditiva sobre la de Business Core — mismo patrón que cualquier cambio
de esquema posterior (ver `docs/database.md`).

## Qué NO hay todavía, y por qué

Ver el detalle completo y actualizado en `ROADMAP.md`, sección "Fase
16 — Business Core" — ahí queda registrado qué entidad está pendiente
porque le falta un segundo proyecto real, y cuál está deliberadamente
congelada (🧊) porque no hay necesidad real, no porque falte tiempo.
En resumen, a la fecha de este documento:

- **`Orders` no está generalizado.** Las dos formas reales conocidas
  (descripción libre en el taller vs. líneas de detalle en el retail)
  divergen demasiado para generalizar sin adivinar — se retoma cuando
  el segundo proyecto tenga una implementación real que comparar.
- **Multi-tenant (`business_id`) está congelado.** Ningún proyecto real
  necesita que varios negocios compartan un despliegue — cada cliente
  tiene su propio despliegue independiente. Agregar `business_id` ahora
  sería diseñar para un escenario SaaS que todavía no existe.
- **`Products`, `Payments`, `Deliveries`, `Files`, `Reports`**: sin
  segunda necesidad real confirmada — excepto `Products`, que si está
  construido (ver más abajo), por una decisión explícita, no por la
  regla automática.

## `Products` / `Categories`

A diferencia de `Customers` (2+ proyectos reales con la misma forma),
esto entró por una decisión explícita del dueño del proyecto, no por la
regla automática: catálogo + inventario + acceso admin es
**independiente del tipo de producto** que se venda — a diferencia de
"Ropa" (talla/color) o "Bordados" (producción a medida), que sí son
específicos de un vertical y siguen sin construirse por falta de
evidencia. Cualquier proyecto futuro que venda productos física o
digitalmente va a necesitar esta base tal cual.

```php
use Misi\Business\Products\CategoryRepository;
use Misi\Business\Products\ProductRepository;

$categories = new CategoryRepository(app()->database());
$products = new ProductRepository(app()->database());

$products->all();              // incluye category_name (LEFT JOIN)
$products->findOrFail(5);
$products->create($data);      // $data ya validado con $products->rulesForCreate()
$products->update(5, $data);
$products->delete(5);

// Ajuste de stock: atómico, nunca deja stock_quantity en negativo.
// $delta positivo = entrada, negativo = salida/venta.
$products->adjustStock(5, -3);
// Lanza Misi\Business\Products\InsufficientStockException (422) si el
// ajuste dejaría el stock en negativo.
```

Stock **simple**: un número por producto, sin variantes de talla/color.
Es una decisión deliberada de alcance para este primer corte, no una
limitación técnica — se agregan variantes el día que un proyecto real
las necesite (probablemente junto con `Modules\Ropa`).

Migraciones: `business/migrations/002_create_categories_table.php` y
`003_create_products_table.php`. `products.category_id` es
`ON DELETE SET NULL`: borrar una categoría nunca borra ni bloquea el
borrado de sus productos.

## El módulo `Modules\Catalog` (Fase 17)

Business Core (`business/Products/`) es la capa de datos —
framework-agnóstica, sin rutas ni HTML. `modules/Catalog/` es la capa
de acceso sobre esos datos: rutas protegidas por sesión, permisos
(`categories.manage`, `products.manage`, `inventory.manage` — via
`Auth::can()`, Fase 6) y un panel HTML de administración
(`GET /modules/catalog/panel`) construido con el kit de UI de la Fase
13 (`public/css/misi.css` + `public/js/api.js`/`ui.js`), sin motor de
plantillas ni frameworks de frontend.

Es exactamente el patrón de capas que describe la introducción del
proyecto: Framework (Database, Validator, Auth) → Business Core
(`ProductRepository`) → Module (`Modules\Catalog`, rutas + UI) →
Application (el proyecto de un cliente real, que copia `business/` y
`modules/Catalog/` y los usa tal cual, o los adapta).

Un proyecto que use este módulo necesita sembrar los tres permisos
(`categories.manage`, `products.manage`, `inventory.manage`) y
asignarlos a los roles que correspondan en su propio `DatabaseSeeder`
— el módulo no siembra nada por sí mismo, igual que
`examples/demo-app` siembra `orders.manage` en el suyo.



`bin/biz` (y su equivalente para hosting sin SSH,
`deploy/infinityfree/web-runner.php`) revisan si existe
`business/migrations/` en el proyecto y, si existe, la agregan como
fuente adicional del `Migrator` — el mismo mecanismo que ya usan los
módulos (Fase 10), solo que con un label fijo `Business` en vez de uno
por módulo. Un proyecto que no copió `business/` simplemente no tiene
esa carpeta, y `bin/biz migrate` sigue funcionando igual que siempre
(la fuente se agrega solo si el directorio existe).

## Cómo se lleva esto a un proyecto nuevo

Igual que `framework/`: se copia la carpeta `business/` completa al
proyecto nuevo, junto a `framework/`. No hay instalación vía Composer
todavía (no hay un paquete privado publicado) — es el mismo modelo de
reutilización manual que el resto de Misi, documentado así a propósito
mientras el framework en sí sigue sin depender de Composer para
funcionar (ver `docs/architecture.md`, "Estrategia de autoloading").
