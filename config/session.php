<?php

declare(strict_types=1);

use Misi\Support\Env;

return [
    'cookie_name' => Env::get('SESSION_COOKIE', 'misi_session'),

    // 0 = la cookie expira al cerrar el navegador
    'lifetime' => (int) Env::get('SESSION_LIFETIME', 0),

    // En producción se fuerza automáticamente vía APP_ENV, pero puede
    // forzarse explícitamente con SESSION_SECURE si el proyecto lo necesita
    // (ej. detrás de un proxy que ya termina TLS).
    'secure' => Env::get('SESSION_SECURE', Env::get('APP_ENV', 'production') === 'production'),

    'same_site' => Env::get('SESSION_SAME_SITE', 'Lax'),
];
