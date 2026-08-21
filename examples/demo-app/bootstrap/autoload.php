<?php

declare(strict_types=1);

/**
 * Autoload de la demo. A propósito NO tiene su propia copia de
 * framework/: reutiliza exactamente el mismo framework/ del proyecto
 * Misi del que partió, vía ruta relativa. Esto demuestra en código la
 * premisa central del proyecto — "construir una vez, reutilizar en
 * múltiples proyectos" — en vez de solo describirla en la documentación.
 *
 * App\ y Modules\ sí son propios de esta demo (cada proyecto tiene los
 * suyos). Si vendor/autoload.php existe (composer install corrido desde
 * la raíz de Misi), se usa el autoloader de Composer en su lugar.
 */

$vendorAutoload = dirname(__DIR__, 3) . '/vendor/autoload.php';

if (is_file($vendorAutoload)) {
    require $vendorAutoload;
    return;
}

spl_autoload_register(function (string $class): void {
    $map = [
        'Misi\\' => dirname(__DIR__, 3) . '/framework/', // compartido con el proyecto padre
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
