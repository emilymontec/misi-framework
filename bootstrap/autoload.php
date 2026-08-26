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

// Fase 15 (auditoría de hosting compartido): Validator (Fase 5) usa
// mb_strlen() para medir longitud de strings correctamente con acentos
// y UTF-8 (ver docs/validation.md) — sin la extensión `mbstring`, esa
// llamada falla con un "Call to undefined function" críptico, en medio
// de una request real, lejos de donde está el problema real. mbstring
// viene habilitada por defecto en la inmensa mayoría de hosting
// compartido (incluido InfinityFree), pero si alguna vez no lo está,
// mejor fallar aquí, de forma clara, que con un 500 fantasma en
// producción. Ver DEPLOYMENT.md.
if (!extension_loaded('mbstring')) {
    $message = "Misi requiere la extensión PHP 'mbstring', que no está "
        . "habilitada en este servidor. En hosting compartido, actívala "
        . "desde el panel de control (selector de versión/extensiones de "
        . "PHP) y vuelve a intentar.\n";

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $message);
    } else {
        http_response_code(500);
        echo $message;
    }

    exit(1);
}

$vendorAutoload = __DIR__ . '/../vendor/autoload.php';

if (is_file($vendorAutoload)) {
    require $vendorAutoload;
    return;
}

spl_autoload_register(function (string $class): void {
    $map = [
        'Misi\\Business\\' => __DIR__ . '/../business/',
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
