<?php

declare(strict_types=1);

use Misi\Support\Env;

return [
    'cookie_name' => Env::get('SESSION_COOKIE', 'bordados_demo_session'),
    'lifetime' => (int) Env::get('SESSION_LIFETIME', 0),
    'secure' => Env::get('SESSION_SECURE', Env::get('APP_ENV', 'production') === 'production'),
    'same_site' => Env::get('SESSION_SAME_SITE', 'Lax'),
];
