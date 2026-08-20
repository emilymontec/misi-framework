# Authentication

`Misi\Auth\Auth` autentica usuarios contra una tabla `users` (creada por
la migración de ejemplo `001_create_users_table.php`) usando sesiones PHP
nativas. API estática, consistente con `Session`.

## Requisitos previos

La tabla `users` debe tener al menos `id`, `email`, `password` (hash de
`password_hash()`). Corre las migraciones y el seed de ejemplo para tener
un usuario con quien probar:

```bash
php bin/biz migrate
php bin/biz db:seed   # crea admin@misi.test / changeme
```

## Uso básico

```php
use Misi\Auth\Auth;
use Misi\Http\JsonResponse;
use Misi\Http\Request;

final class SessionController
{
    public function login(Request $request): JsonResponse
    {
        $data = app()->validator()->validate($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($data['email'], $data['password'])) {
            return JsonResponse::error('Credenciales inválidas.', [], 401);
        }

        return JsonResponse::success(Auth::user(), 'Sesión iniciada');
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        return JsonResponse::success(null, 'Sesión cerrada');
    }
}
```

## API de Auth

| Método | Qué hace |
|---|---|
| `Auth::attempt(email, password)` | Verifica credenciales con `password_verify()`. Si son correctas, inicia sesión (regenerando el ID) y retorna `true`. |
| `Auth::login(userId)` | Inicia sesión para un ID ya verificado (ej. justo después de un registro, sin pasar por `attempt()`). |
| `Auth::logout()` | Destruye la sesión por completo (`Session::destroy()`). |
| `Auth::check()` | `true` si hay un usuario autenticado. |
| `Auth::guest()` | Lo contrario de `check()`. |
| `Auth::id()` | ID del usuario autenticado, o `null`. |
| `Auth::user()` | Fila completa del usuario autenticado (sin el campo `password`), o `null`. |
| `Auth::can(permiso)` | Ver `docs/authorization.md`. |

## Middleware `auth` y `guest`

Registrados automáticamente por `Application` — no hay que configurarlos
en cada proyecto:

```php
$router->get('/perfil', [ProfileController::class, 'show'], ['auth']);
$router->post('/login', [SessionController::class, 'login'], ['guest']);
```

- `auth`: si no hay sesión, corta la petición con `401` (JSON estándar de
  error) antes de llegar al controlador.
- `guest`: si ya hay sesión, corta con `403` — útil para que un usuario
  logueado no pueda volver a pegarle a `/login`.

Cada proyecto puede reemplazar el comportamiento de estos alias (por
ejemplo, para redirigir a una página de login en vez de devolver JSON)
llamando de nuevo a `$router->aliasMiddleware('auth', ...)` con su propia
lógica.

## Seguridad

- Las contraseñas se guardan siempre con `password_hash()` (nunca texto
  plano) y se verifican con `password_verify()`.
- `Auth::attempt()` y `Auth::login()` regeneran el ID de sesión **antes**
  de guardar el usuario autenticado — mitiga session fixation (un ID de
  sesión robado antes del login queda invalidado).
- Las cookies de sesión se configuran de forma segura desde
  `config/session.php`: `HttpOnly` siempre, `Secure` automático cuando
  `APP_ENV=production`, `SameSite` configurable.
- `Auth::user()` nunca expone el hash de la contraseña.

## Qué NO hace Auth (por ahora)

- No implementa JWT ni OAuth — es autenticación por sesión, pensada para
  que el frontend y el backend compartan cookies (mismo dominio o CORS
  configurado con credenciales).
- No implementa "remember me" persistente más allá de la sesión.
- No soporta múltiples "guards" (varios tipos de usuario autenticándose
  por separado, ej. clientes vs. administradores en tablas distintas). Se
  evalúa solo si un proyecto real lo necesita.
