# Database

`Misi\Database\Database` envuelve PDO. Se accede vía `app()->database()`
(conexión perezosa: no se conecta a MySQL hasta la primera consulta real).

## Consultas

```php
$db = app()->database();

$customers = $db->select('SELECT * FROM customers WHERE business_id = ?', [$businessId]);
$customer = $db->selectOne('SELECT * FROM customers WHERE id = ?', [$id]); // array o null

$id = $db->insert('customers', ['name' => 'Ana', 'email' => 'ana@example.com']);
$rows = $db->update('customers', ['name' => 'Ana P.'], 'id = ?', [$id]);
$rows = $db->delete('customers', 'id = ?', [$id]);

$statement = $db->query('SELECT COUNT(*) AS total FROM customers'); // PDOStatement crudo si lo necesitas
```

100% prepared statements — nunca se concatena input del usuario en el SQL,
en ningún método. `$table` y las claves de `$data` en `insert()`/`update()`/
`delete()` sí se interpolan directamente (PDO no permite parametrizar
identificadores, solo valores), pero se validan contra
`^[a-zA-Z_][a-zA-Z0-9_]*$` antes — un nombre de columna/tabla inválido
lanza `DatabaseException` en vez de llegar a ejecutarse (Fase 15, ver
`docs/security.md`). `where` en `update()`/`delete()` queda fuera de esa
validación a propósito: ahí siempre viaja SQL real (`'id = ? AND status = ?'`),
no un identificador simple — sigue siendo responsabilidad de quien la
escribe, igual que cualquier SQL crudo del proyecto.

## Transacciones

```php
$db->transaction(function ($db) {
    $orderId = $db->insert('orders', [...]);
    $db->insert('order_items', ['order_id' => $orderId, ...]);
    // si algo lanza una excepción aquí, se hace rollback automático
});
```

O manualmente: `$db->beginTransaction() / $db->commit() / $db->rollBack()`.

## Migrations

Archivos PHP numerados en `database/migrations/` (el prefijo numérico
define el orden). Cada uno retorna una clase anónima que extiende
`Misi\Database\Migration`:

```php
<?php
use Misi\Database\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS products (...)');
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS products');
    }
};
```

SQL crudo, sin DSL de esquema — deliberado, ver `docs/architecture.md`
sección 8.

```bash
php bin/biz migrate            # ejecuta pendientes
php bin/biz migrate:status      # qué corrió y qué falta, por lote
php bin/biz migrate:rollback    # revierte el último lote completo
```

La tabla `migrations` (columna `batch`) se crea sola en el primer uso.

Si un proyecto necesita agregar un índice (u otro cambio de esquema) a
una tabla que ya tiene datos en producción, se hace con una migración
nueva y aditiva — nunca editando una migración que ya corrió. Ejemplo
real: `database/migrations/004_add_role_user_user_id_index.php`
(Fase 15, agregó un índice que faltaba sin tocar
`002_create_roles_and_permissions.php`).

## Seeders

`database/seeders/DatabaseSeeder.php` es el punto de entrada
convencional. Para varios seeders, este archivo los orquesta llamando a
otros:

```php
<?php
use Misi\Database\Seeder;

return new class extends Seeder {
    public function run(): void
    {
        // $this->insert('customers', [...]);
        // o $this->db->query(...) para lo que no sea un simple insert
    }
};
```

```bash
php bin/biz db:seed
```

Ver el seeder de ejemplo (usuario admin + rol + permiso) para el patrón
`firstOrCreate` que evita duplicar datos si el seed se corre más de una vez.

## Qué NO hace Database (a propósito, 🧊 congelado)

- **No es un ORM.** No hay modelos con relaciones, eager loading, ni
  scopes. Los controladores/servicios trabajan con arrays asociativos
  directamente.
- **No hay Query Builder.** Ni siquiera uno mínimo, todavía. Se evalúa
  (Fase 4.1) solo si 2+ proyectos repiten el mismo patrón de armar SQL
  dinámico de forma incómoda con el `Database` actual. Límite explícito
  si algún día se implementa: en cuanto necesite joins complejos, eager
  loading o scopes, se detiene — eso ya no es el objetivo de Misi.
- **No abstrae el motor de base de datos.** Está pensado para MySQL/MariaDB
  específicamente (DSN hardcodeado a `mysql:`), no para ser
  agnóstico de motor.
