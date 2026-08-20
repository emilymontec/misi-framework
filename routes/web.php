<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthDemoController;
use App\Http\Controllers\Api\PingController;
use App\Http\Controllers\Api\UploadDemoController;
use App\Http\Controllers\Api\ValidationDemoController;
use App\Http\Controllers\UiKitController;
use App\Http\Controllers\WelcomeController;
use Misi\Http\JsonResponse;
use Misi\Http\Request;

/** @var \Misi\Routing\Router $router */

$router->get('/', [WelcomeController::class, 'index']);
$router->get('/saludo/{name}', [WelcomeController::class, 'greet']);
$router->get('/ui-kit', [UiKitController::class, 'index']);
$router->get('/api/ping', [PingController::class, '__invoke']);
$router->post('/api/validate-demo', [ValidationDemoController::class, '__invoke']);

$router->post('/api/login', [AuthDemoController::class, 'login'], ['guest']);
$router->post('/api/logout', [AuthDemoController::class, 'logout'], ['auth', 'csrf']);
$router->get('/api/me', [AuthDemoController::class, 'me'], ['auth']);

// Ruta de solo lectura (sin middleware csrf, GET no muta estado) que
// entrega el token para que un cliente fetch/AJAX lo mande luego en el
// header X-CSRF-Token de sus peticiones POST/PUT/PATCH/DELETE.
$router->get('/api/csrf-token', fn (Request $request) => JsonResponse::success([
    'token' => csrf_token(),
]));

$router->post('/api/uploads', [UploadDemoController::class, 'store'], ['auth', 'csrf']);
$router->delete('/api/uploads/{id}', [UploadDemoController::class, 'destroy'], ['auth', 'csrf']);
$router->get('/storage/{path*}', [UploadDemoController::class, 'show']);
