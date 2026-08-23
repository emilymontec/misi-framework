<?php

declare(strict_types=1);

use Modules\Catalog\Controllers\CategoryController;
use Modules\Catalog\Controllers\PanelController;
use Modules\Catalog\Controllers\ProductController;

/**
 * Rutas del módulo Catalog. Todas requieren sesión ('auth') — es un
 * panel administrativo, no un catálogo público. Las acciones que
 * modifican datos además verifican un permiso específico dentro del
 * controlador ('categories.manage' / 'products.manage' /
 * 'inventory.manage' — ver docs/authorization.md sobre por qué esa
 * verificación vive en el controlador y no en el middleware).
 *
 * El proyecto que use este módulo es responsable de sembrar esos
 * permisos (via su propio DatabaseSeeder, igual que examples/demo-app
 * siembra 'orders.manage') y asignarlos a los roles que correspondan.
 *
 * @var \Misi\Routing\Router $router
 */

$router->get('/modules/catalog/panel', [PanelController::class, 'index'], ['auth']);

$router->get('/modules/catalog/categories', [CategoryController::class, 'index'], ['auth']);
$router->post('/modules/catalog/categories', [CategoryController::class, 'store'], ['auth', 'csrf']);
$router->put('/modules/catalog/categories/{id}', [CategoryController::class, 'update'], ['auth', 'csrf']);
$router->delete('/modules/catalog/categories/{id}', [CategoryController::class, 'destroy'], ['auth', 'csrf']);

$router->get('/modules/catalog/products', [ProductController::class, 'index'], ['auth']);
$router->get('/modules/catalog/products/{id}', [ProductController::class, 'show'], ['auth']);
$router->post('/modules/catalog/products', [ProductController::class, 'store'], ['auth', 'csrf']);
$router->put('/modules/catalog/products/{id}', [ProductController::class, 'update'], ['auth', 'csrf']);
$router->delete('/modules/catalog/products/{id}', [ProductController::class, 'destroy'], ['auth', 'csrf']);
$router->post('/modules/catalog/products/{id}/stock', [ProductController::class, 'adjustStock'], ['auth', 'csrf']);
