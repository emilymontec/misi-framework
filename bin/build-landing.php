<?php

declare(strict_types=1);

/**
 * Genera las páginas estáticas del sitio de Misi a partir de piezas en
 * public/views/:
 *
 *   _partials/   cabecera, ticker, footer — compartidos por TODAS las
 *                páginas (excepto ui-kit.html, que es autocontenida).
 *   _content/    el contenido propio de cada página (sin <html>/<head>
 *                /<main> — el build los envuelve).
 *
 * Cada página sigue siendo HTML estático de verdad al final — nada de
 * PHP en cada request. El .htaccess raíz sirve "/" -> index.html y
 * "/{page}" -> public/views/{page}.html directamente (ver regla
 * "vistas con URL limpia"), así que esto solo es una comodidad de
 * desarrollo: editas la pieza que corresponda y vuelves a correr este
 * script.
 *
 * Uso: php bin/build-landing.php
 */

$root = dirname(__DIR__);
$partialsDir = $root . '/public/views/_partials';
$contentDir = $root . '/public/views/_content';

/**
 * outputPath === null  -> se escribe en la raíz del proyecto (index.html)
 * outputPath === 'x'   -> se escribe en public/views/x.html (URL limpia /x)
 */
$pages = [
    [
        'content' => 'home.html',
        'output' => null,
        'activeNav' => 'inicio',
        'title' => 'Misi — Base PHP para sistemas administrativos',
        'description' => 'Misi — base de desarrollo en PHP, ligera y sin dependencias, para construir sistemas administrativos de pequeños negocios sin reinventar routing, auth, CSRF o validación.',
    ],
    [
        'content' => 'misi.html',
        'output' => 'misi',
        'activeNav' => 'misi',
        'title' => 'Qué es Misi — Características',
        'description' => 'Qué es Misi y qué incluye de fábrica: router con middleware, PDO sin ORM, validación, auth/CSRF con RBAC, storage y CLI — pensado para hosting compartido.',
    ],
    [
        'content' => 'cli.html',
        'output' => 'cli',
        'activeNav' => 'cli',
        'title' => 'Misi CLI — Comandos',
        'description' => 'Referencia de comandos del CLI de Misi: servidor y diagnóstico, base de datos, generadores make:* y Business Core.',
    ],
    [
        'content' => 'routes.html',
        'output' => 'routes',
        'activeNav' => 'routes',
        'title' => 'De ruta a respuesta — Misi',
        'description' => 'Cómo se ve un endpoint típico en Misi: ruta, validación y guardado en base de datos, sin ceremonia.',
    ],
    [
        'content' => 'instalacion.html',
        'output' => 'instalacion',
        'activeNav' => 'instalacion',
        'title' => 'Instalación — Misi',
        'description' => 'De cero a servidor corriendo en 5 pasos: instalar el CLI, crear el proyecto, configurar el entorno, migrar y arrancar.',
    ],
];

function readPartial(string $dir, string $file): string
{
    $path = $dir . '/' . $file;

    if (!is_file($path)) {
        fwrite(STDERR, "Falta el parcial: {$file} (esperado en {$path})\n");
        exit(1);
    }

    return file_get_contents($path);
}

$head = readPartial($partialsDir, 'head.html');
$header = readPartial($partialsDir, 'header.html');
$ticker = readPartial($partialsDir, 'ticker.html');
$footer = readPartial($partialsDir, 'footer.html');

foreach ($pages as $page) {
    $contentPath = $contentDir . '/' . $page['content'];

    if (!is_file($contentPath)) {
        fwrite(STDERR, "Falta el contenido: {$page['content']} (esperado en {$contentPath})\n");
        exit(1);
    }

    $content = file_get_contents($contentPath);

    // Resalta en el nav el link de la página actual (data-nav="...").
    $headerForPage = preg_replace(
        '/<a href="([^"]+)" data-nav="' . preg_quote($page['activeNav'], '/') . '">/',
        '<a href="$1" data-nav="' . $page['activeNav'] . '" class="active">',
        $header
    );

    $headForPage = str_replace(
        ['{{TITLE}}', '{{DESCRIPTION}}'],
        [htmlspecialchars($page['title'], ENT_QUOTES), htmlspecialchars($page['description'], ENT_QUOTES)],
        $head
    );

    $html = "<!DOCTYPE html>\n<html lang=\"es\">\n\n"
        . $headForPage
        . $headerForPage
        . "\n"
        . $ticker
        . "\n  <!-- CONTENIDO PRINCIPAL -->\n  <main class=\"container\" id=\"contenido\">\n"
        . $content
        . "\n  </main>\n\n"
        . $footer
        . "\n  <script src=\"/public/js/landing.js\"></script>\n\n</body>\n\n</html>\n";

    $html = preg_replace("/\n{3,}/", "\n\n", $html);

    $outputPath = $page['output'] === null
        ? $root . '/index.html'
        : $root . '/public/views/' . $page['output'] . '.html';

    file_put_contents($outputPath, $html);
    $label = $page['output'] === null ? 'index.html' : "public/views/{$page['output']}.html";
    echo "generado: {$label}\n";
}
