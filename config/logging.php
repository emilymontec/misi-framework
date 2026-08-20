<?php

declare(strict_types=1);

use Misi\Support\Env;

return [
    // debug | info | warning | error | critical — cualquier nivel por
    // debajo de este se descarta silenciosamente. En producción suele
    // configurarse en 'warning' o 'error' para no llenar el disco con
    // ruido de debug.
    'level' => Env::get('LOG_LEVEL', 'debug'),

    'path' => 'storage/logs',
];
