<?php

declare(strict_types=1);

use Misi\Support\Env;

return [
    'name' => Env::get('APP_NAME', 'Misi'),
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => Env::get('APP_DEBUG', false),
    'url' => Env::get('APP_URL', 'http://localhost'),
    'timezone' => Env::get('APP_TIMEZONE', 'UTC'),
];
