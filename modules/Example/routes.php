<?php

declare(strict_types=1);

use Modules\Example\Controllers\PingController;

/**
 * Rutas del módulo Example. Application::loadRoutes() ya expone $router
 * aquí con la misma API que routes/web.php — no hace falta importar ni
 * inicializar nada más.
 *
 * @var \Misi\Routing\Router $router
 */

$router->get('/modules/example/ping', [PingController::class, '__invoke']);
