<?php

declare(strict_types=1);

/**
 * Autoload de respaldo (sin Composer).
 *
 * Si vendor/autoload.php existe (composer install), se usa el
 * autoloader PSR-4 oficial de Composer. Si no, este mapeo manual
 * permite que Misi funcione en cualquier hosting compartido sin
 * necesidad de conexión a internet ni Composer instalado.
 */

$vendorAutoload = __DIR__ . '/../vendor/autoload.php';

if (is_file($vendorAutoload)) {
    require $vendorAutoload;
    return;
}

spl_autoload_register(function (string $class): void {
    $map = [
        'Misi\\' => __DIR__ . '/../framework/',
        'App\\' => __DIR__ . '/../app/',
        'Modules\\' => __DIR__ . '/../modules/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require $file;
            return;
        }
    }
});
