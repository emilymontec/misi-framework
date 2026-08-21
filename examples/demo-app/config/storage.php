<?php

declare(strict_types=1);

use Misi\Support\Env;

return [
    'driver' => Env::get('STORAGE_DRIVER', 'local'),
    'path' => Env::get('STORAGE_PATH', 'storage/uploads'),
];
