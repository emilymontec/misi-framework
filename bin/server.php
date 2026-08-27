<?php

declare(strict_types=1);

/**
 * Router script EXCLUSIVO del servidor embebido de PHP (`misi serve`). Nunca se usa en producción — Apache/Nginx sirven public/
 * directamente, sin pasar por este archivo.
 *
 * Por qué existe en vez de usar public/index.php directamente como
 * router script: cuando `php -S` invoca un router script, ese script
 * recibe TODAS las peticiones, incluidas las de archivos estáticos
 * reales (css, js, imágenes). Si el router script no le devuelve el
 * control al servidor embebido para esos casos (con `return false;`),
 * el servidor nunca los sirve — y como public/index.php arranca la
 * aplicación incondicionalmente, /css/misi.css terminaba devolviendo
 * el 404 JSON de Misi en vez del archivo real. Este router resuelve
 * exactamente ese caso, sin tocar public/index.php (que debe seguir
 * siendo idéntico a como lo vería Apache/Nginx en producción).
 */

$publicPath = dirname(__DIR__) . '/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$filePath = $publicPath . $uri;

// Si la URL corresponde a un archivo real dentro de public/ (y no a
// public/index.php mismo, que sí debe pasar por la app), que el
// servidor embebido lo sirva tal cual — mismo comportamiento que
// tendría Apache/Nginx con un archivo estático.
if ($uri !== '/' && is_file($filePath) && basename($filePath) !== 'index.php') {
    return false;
}

require $publicPath . '/index.php';
