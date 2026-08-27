<?php

declare(strict_types=1);

/**
 * Genera index.html (raíz del proyecto) a partir de las secciones en
 * public/views/landing/. Se corre a mano cada vez que edites una
 * sección — index.html sigue siendo HTML estático de verdad (así
 * sigue funcionando tal cual con la regla [END] de .htaccess, sin
 * pasar por PHP en producción); esta build es solo una comodidad de
 * desarrollo para no editar 1000+ líneas en un único archivo.
 *
 * Orden de ensamblado: ver $sections abajo. Cada archivo es un
 * fragmento HTML plano (sin <html>/<head>/<body> propios).
 *
 * Uso: php bin/build-landing.php
 */

$root = dirname(__DIR__);
$viewsDir = $root . '/public/views/landing';

$sections = [
    'head.html',
    'header.html',
    'ticker.html',
    'hero.html',
    'quick-install.html',
    'subsistemas.html',
    'caracteristicas.html',
    'instalacion.html',
    'ejemplo.html',
    'cli.html',
    'footer.html',
];

$html = "<!DOCTYPE html>\n<html lang=\"es\">\n\n";

foreach ($sections as $file) {
    $path = $viewsDir . '/' . $file;

    if (!is_file($path)) {
        fwrite(STDERR, "Falta la sección: {$file} (esperada en {$path})\n");
        exit(1);
    }

    $html .= file_get_contents($path);
}

$html .= "\n  <script src=\"public/js/landing.js\"></script>\n\n</body>\n\n</html>\n";
$html = preg_replace("/\n{3,}/", "\n\n", $html);

$outputPath = $root . '/index.html';
file_put_contents($outputPath, $html);

echo "index.html generado a partir de " . count($sections) . " secciones en public/views/landing/\n";
