# HTTP

Abstracciones sobre el ciclo request/response: `Request`, `Response`,
`JsonResponse`, `RedirectResponse`.

## Request

Se construye una única vez por petición vía `Request::capture()` (lo hace
`Application::run()` automáticamente — no se instancia a mano en un
proyecto normal). Es un snapshot inmutable de las superglobales.

```php
$request->method();          // 'GET', 'POST'... (soporta _method para PUT/PATCH/DELETE en forms)
$request->uri();             // '/customers/42' (sin query string)
$request->all();             // query + post, o query + JSON si Content-Type: application/json
$request->input('name');     // atajo de all()['name']
$request->query('page');     // solo $_GET
$request->post('name');      // solo $_POST
$request->header('Authorization');
$request->cookie('theme');
$request->file('avatar');    // estructura de $_FILES, o null si no se subió nada
$request->session('user_id'); // atajo de Misi\Support\Session::get()
$request->ip();
$request->isJson();
```

`all()` combina query string y body: si el `Content-Type` es
`application/json`, decodifica el body; si no, usa `$_POST`. Esto permite
que los controladores llamen siempre a `$request->all()` sin preocuparse
de si el cliente mandó un formulario tradicional o JSON.

## Response

Clase base de las tres siguientes. Rara vez se instancia directamente
salvo para HTML plano:

```php
return new Response('<h1>Hola</h1>');
return new Response('No encontrado', 404);
```

## JsonResponse

Formato estándar de Misi para toda la API:

```php
JsonResponse::success($data, 'Cliente creado', 201);
// {"success": true, "data": {...}, "message": "Cliente creado"}

JsonResponse::error('Validation failed', $errores, 422);
// {"success": false, "data": null, "message": "Validation failed", "errors": {...}}
```

`Application::handleException()` ya usa este formato automáticamente para
`HttpException`, `NotFoundException` y `ValidationException` — no hace
falta capturar esas excepciones a mano en cada controlador.

## RedirectResponse

```php
return new RedirectResponse('/login');           // 302 por defecto
return new RedirectResponse('/login', 301);
```

## Qué falta (a propósito)

- `Request` no valida ni sanitiza — eso es trabajo de `Validation` (Fase 5).
- No hay negociación de contenido (`Accept` header) — Misi asume JSON
  para la API y HTML plano para lo demás; no se agrega negociación
  automática salvo que un proyecto real la necesite.
