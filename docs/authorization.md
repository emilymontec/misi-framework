# Authorization

Misi implementa un RBAC (control de acceso por roles) deliberadamente
simple: usuarios tienen roles, roles tienen permisos. Sin jerarquías, sin
permisos condicionales, sin herencia entre roles.

## Esquema (migración `002_create_roles_and_permissions.php`)

```text
users ──┐
        ├─< role_user >─┐
        │                ├─ roles ──< permission_role >── permissions
        └────────────────┘
```

- `roles` — `id`, `name` (único)
- `permissions` — `id`, `name` (único, convención `recurso.accion`)
- `role_user` — tabla pivote (un usuario puede tener varios roles)
- `permission_role` — tabla pivote (un rol puede tener varios permisos)

Corre la migración para tener las tablas:

```bash
php bin/biz migrate
```

## Uso

```php
use Misi\Auth\Auth;

if (!Auth::can('orders.delete')) {
    return JsonResponse::error('No autorizado.', [], 403);
}
```

`Auth::can()` retorna `false` para invitados y para permisos que no
existen — nunca lanza una excepción. La decisión de qué hacer con un
`false` (403, redirigir, ocultar un botón) es del controlador que lo
llama, no de `Auth`.

## Asignar roles y permisos

No hay una API dedicada todavía (`Role::create()`, etc.) — se trabaja
directo contra las tablas, igual que el resto de `Database`:

```php
$db = app()->database();

$roleId = $db->insert('roles', ['name' => 'vendedor', 'created_at' => date('Y-m-d H:i:s')]);
$permissionId = $db->insert('permissions', ['name' => 'orders.create', 'created_at' => date('Y-m-d H:i:s')]);

$db->query('INSERT INTO permission_role (permission_id, role_id) VALUES (?, ?)', [$permissionId, $roleId]);
$db->query('INSERT INTO role_user (role_id, user_id) VALUES (?, ?)', [$roleId, $userId]);
```

Ver `database/seeders/DatabaseSeeder.php` para un ejemplo completo
(`firstOrCreate` evitando duplicados en re-ejecuciones del seed).

## Por qué no hay middleware `can:permiso` genérico

El Router (Fase 3) resuelve middleware por alias simple (`['auth']`), sin
parámetros (`['can:orders.delete']` no está soportado). Agregar
parámetros a los alias de middleware es una abstracción real, pero
todavía no se justifica con un caso de uso concreto — por ahora,
verificar `Auth::can()` directamente dentro del controlador (como hace
`AuthDemoController::me()`) es igual de simple y no complica el Router.
Si varios proyectos reales terminan repitiendo el mismo patrón de
"middleware que verifica un permiso fijo", se evalúa entonces agregar
soporte de parámetros al Router (regla de oro de abstracciones, ver
`docs/architecture.md`).

## Qué NO hace Authorization (por ahora)

- Sin jerarquía de roles (un rol "admin" no hereda automáticamente los
  permisos de "editor").
- Sin permisos con alcance (ej. "solo puede editar sus propios pedidos") —
  eso se sigue validando a mano en el controlador/service, comparando
  `business_id` o `owner_id` contra `Auth::id()`.
- Sin UI de administración de roles/permisos — es responsabilidad de cada
  proyecto (o de un futuro módulo del Business Core, ver Fase 16).
