# Routing

`Misi\Routing\Router`, accesible como `$router` dentro de `routes/web.php`
(inyectado por `Application::loadRoutes()`).

## Rutas básicas

```php
$router->get('/customers', [CustomerController::class, 'index']);
$router->post('/customers', [CustomerController::class, 'store']);
$router->put('/customers/{id}', [CustomerController::class, 'update']);
$router->patch('/customers/{id}', [CustomerController::class, 'update']);
$router->delete('/customers/{id}', [CustomerController::class, 'destroy']);
```

El handler puede ser `[Clase::class, 'metodo']` o un closure:

```php
$router->get('/status', fn (Request $request) => JsonResponse::success(['ok' => true]));
```

Controladores se instancian con `new $class()` (sin contenedor de DI, ver
`docs/architecture.md` "Riesgos técnicos") — si necesitan `Database`,
`Validator`, etc., se accede vía el helper `app()`.

## Parámetros de ruta

```php
$router->get('/customers/{id}', [CustomerController::class, 'show']);
```

```php
public function show(Request $request, string $id): JsonResponse { ... }
```

Los parámetros llegan como argumentos posicionales adicionales al método,
en el mismo orden en que aparecen en la URI. Siempre como `string` — la
conversión a `int` u otro tipo es responsabilidad del controlador o de
`Validation`.

### Parámetro catch-all (`{param*}`)

Un `{id}` normal no matchea `/` — no sirve para rutas con subdirectorios.
Para eso existe `{param*}`, agregado en la Fase 8 porque `Storage` lo
necesitaba de verdad para servir archivos (`avatars/foo.jpg`):

```php
$router->get('/storage/{path*}', [UploadController::class, 'show']);
// GET /storage/avatars/3f9a2b1c....jpg -> $path = "avatars/3f9a2b1c....jpg"
```

## Middleware

```php
$router->get('/perfil', [ProfileController::class, 'show'], ['auth']);
```

`auth` y `guest` ya están registrados por `Application` (ver
`docs/authentication.md`). Para agregar uno propio:

```php
$router->aliasMiddleware('admin', function (Request $request, callable $next) {
    if (!Auth::can('admin.access')) {
        return JsonResponse::error('No autorizado.', [], 403);
    }
    return $next($request);
});
```

Un middleware recibe el `Request` y un `$next` callable; puede:

- devolver un `Response` propio para cortar la cadena (ej. un 401/403), o
- llamar a `$next($request)` y devolver su resultado para continuar.

El pipeline se ejecuta en el orden declarado en el array de la ruta.

## Ruta no encontrada

Si ninguna ruta hace match, `Router::dispatch()` lanza
`NotFoundException`, que `Application::handleException()` traduce a un
404 en el formato JSON estándar — sin código adicional en cada proyecto.

## Qué NO hace el Router (a propósito, 🧊 congelado)

- **Grupos de rutas con prefijo** (`$router->group('/api', fn ($r) => ...)`).
  Se agrega solo si 2+ proyectos reales repiten el mismo prefijo de forma
  incómoda — hasta ahora, escribir `/api/...` en cada ruta no ha sido un
  problema real.
- **Rutas nombradas + generación de URL** (`route('customers.show', ['id' => 1])`).
  Mismo criterio: se evalúa cuando la repetición de URLs hardcodeadas
  duela de verdad en un proyecto concreto.
- **Route caching.** Con la cantidad de rutas que maneja un sistema
  administrativo pequeño, el costo de recompilar los patrones en cada
  request es insignificante.

Ver la "regla de oro de abstracciones" en `docs/architecture.md` — estas
tres cosas no están descartadas para siempre, solo no se han ganado su
lugar todavía.
