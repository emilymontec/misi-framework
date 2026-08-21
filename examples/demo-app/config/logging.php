<?php

declare(strict_types=1);

use Misi\Support\Env;

return [
    'level' => Env::get('LOG_LEVEL', 'debug'),
    'path' => 'storage/logs',
];
