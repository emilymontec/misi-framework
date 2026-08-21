<?php

declare(strict_types=1);

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SessionController;
use Misi\Http\JsonResponse;
use Misi\Http\Request;

/** @var \Misi\Routing\Router $router */

$router->get('/', [HomeController::class, 'index']);

$router->get('/api/csrf-token', fn (Request $request) => JsonResponse::success([
    'token' => csrf_token(),
]));

$router->post('/api/login', [SessionController::class, 'login'], ['guest']);
$router->post('/api/logout', [SessionController::class, 'logout'], ['auth', 'csrf']);
$router->get('/api/me', [SessionController::class, 'me'], ['auth']);

$router->get('/api/customers', [CustomerController::class, 'index'], ['auth']);
$router->get('/api/customers/{id}', [CustomerController::class, 'show'], ['auth']);
$router->post('/api/customers', [CustomerController::class, 'store'], ['auth', 'csrf']);
$router->put('/api/customers/{id}', [CustomerController::class, 'update'], ['auth', 'csrf']);
$router->delete('/api/customers/{id}', [CustomerController::class, 'destroy'], ['auth', 'csrf']);

$router->get('/api/orders', [OrderController::class, 'index'], ['auth']);
$router->get('/api/orders/{id}', [OrderController::class, 'show'], ['auth']);
$router->post('/api/orders', [OrderController::class, 'store'], ['auth', 'csrf']);
$router->put('/api/orders/{id}', [OrderController::class, 'update'], ['auth', 'csrf']);
$router->delete('/api/orders/{id}', [OrderController::class, 'destroy'], ['auth', 'csrf']);

// Protegida con 'auth': son fotos de pedidos de clientes, no archivos públicos.
$router->get('/storage/{path*}', [OrderController::class, 'showAttachment'], ['auth']);
