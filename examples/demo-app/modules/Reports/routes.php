<?php

declare(strict_types=1);

use Modules\Reports\Controllers\SummaryController;

/** @var \Misi\Routing\Router $router */

$router->get('/modules/reports/summary', [SummaryController::class, '__invoke'], ['auth']);
