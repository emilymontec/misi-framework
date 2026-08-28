<?php

declare(strict_types=1);

use Misi\Core\Application;

require __DIR__ . '/autoload.php';
require dirname(__DIR__) . '/.misi/Support/helpers.php';

$app = new Application(dirname(__DIR__));
$app->loadRoutes(dirname(__DIR__) . '/routes/web.php');

return $app;
