# Bordados Ana — demo de Misi (Fase 14)

Aplicación de ejemplo completa: un pequeño taller de bordados que
gestiona clientes y pedidos. Existe para demostrar todas las fases de
Misi funcionando **juntas**, en un caso de uso real — no es parte del
framework (`framework/` no sabe que esto existe) ni del scaffold base
(`app/`, `modules/` de la raíz del proyecto). Bórrala sin miedo si no la
necesitas; el framework funciona exactamente igual sin ella.

## Qué demuestra, y dónde mirarlo

| Fase | Qué hace la demo | Dónde |
|---|---|---|
| Bootstrap | Reutiliza el `framework/` del proyecto padre por ruta relativa — **no** tiene su propia copia | `bootstrap/autoload.php` |
| Router | Rutas con parámetros, middleware `auth`/`csrf`/`guest` | `routes/web.php` |
| Database | CRUD completo sobre `customers`/`orders`, transacciones vía FK con `ON DELETE CASCADE` | `app/Http/Controllers/CustomerController.php` |
| Validation | `required`, `email`, `unique` (con excepción propia al editar), `exists`, `image`, `mimes`, `max_size`, `in` | Ambos controladores |
| Auth | Login por sesión, usuario de staff | `app/Http/Controllers/SessionController.php` |
| Security | CSRF automático en toda mutación, RBAC (`orders.manage`) verificado a mano en el controlador | `OrderController::destroy()` |
| Storage | Subida de imagen de referencia con validación real de contenido, servida **detrás de `auth`** (a diferencia de la demo pública del proyecto padre — aquí sí tiene sentido exigir sesión) | `OrderController::store()` / `showAttachment()` |
| Logging | Heredado sin cambios — cualquier error 5xx real se registra igual que en el proyecto padre | — |
| Modules | Un módulo real (`Reports`) que resuelve un caso de negocio genuino (resumen de clientes/pedidos), no solo un ping | `modules/Reports/` |
| CLI | `bin/biz` propio (copia, no symlink — cada proyecto tiene el suyo) | `bin/biz` |
| Generadores | Estructura lista para `make:controller`, etc. si quieres extender la demo | `resources/stubs/` |
| UI utilities | Interfaz real (no solo curl): login, tablas, modales, formularios — usando `public/css/misi.css` + `public/js/api.js` + `public/js/ui.js` tal cual, sin modificarlos | `resources/views/app.php` |

## Por qué reutiliza `framework/` en vez de copiarlo

`bootstrap/autoload.php` mapea `Misi\` a `../../framework/` (la del
proyecto padre), no a una copia propia. Esto no es solo por ahorrar
espacio: es la demostración en código de la premisa central de todo el
proyecto — **construir el framework una vez, reutilizarlo en múltiples
proyectos** — en vez de solo describirla en la documentación. `App\` y
`Modules\` sí son propios de esta demo, como corresponde a cada proyecto.

## Cómo correrla

```bash
cd examples/demo-app
cp .env.example .env
# edita .env con tus credenciales de MySQL (una base de datos separada
# de la que uses para el proyecto padre, ej. "bordados_demo")

php bin/biz migrate
php bin/biz db:seed
php bin/biz serve
```

Abre `http://localhost:8000` — verás la pantalla de login. Usuario demo
(creado por el seeder):

```text
staff@bordados.test / changeme
```

El seed también crea 2 clientes y 2 pedidos de ejemplo para que la
interfaz no se vea vacía.

## Qué probar

- Crear un cliente nuevo (botón "+ Nuevo cliente") — prueba a repetir un
  email existente para ver el error de validación real.
- Crear un pedido con imagen de referencia — sube una imagen real; si
  subes un archivo que no es una imagen de verdad (aunque tenga
  extensión `.jpg`), Validation lo rechaza por contenido, no por
  extensión.
- Cambiar el estado de un pedido desde la tabla.
- Eliminar un pedido o un cliente (el usuario demo sí tiene el permiso
  `orders.manage`).
- Cerrar sesión y volver a intentar ver `/storage/...` de un pedido sin
  loguearte — a diferencia del avatar público de la demo del proyecto
  padre, aquí las imágenes de pedidos están protegidas con `auth`.

## Diferencias intencionales con las demos del proyecto padre

- **Storage protegido, no público.** Tiene sentido: son fotos de pedidos
  de clientes, no avatares públicos. Ver `docs/storage.md` del proyecto
  padre sobre cómo proteger archivos.
- **Autorización verificada a mano en el controlador**
  (`Auth::can('orders.manage')`), no vía middleware — el Router no
  soporta middleware con parámetros (`['can:orders.manage']`), tal como
  documenta `docs/authorization.md` del proyecto padre.
- **Interfaz real**, no solo endpoints JSON — las demos del proyecto
  padre (`/ui-kit`, `/api/validate-demo`, etc.) son deliberadamente
  aisladas por fase; esta demo es la primera vez que todo se ve
  funcionando junto, en una pantalla, para un caso de uso concreto.
