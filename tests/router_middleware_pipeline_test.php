<?php

declare(strict_types=1);

/**
 * Test de regresión para AUDIT-001 (ver AUDIT_REPORT.md).
 *
 * Verifica el contrato del pipeline de middleware documentado en
 * docs/routing.md: cada middleware debe devolver un Response propio,
 * o el resultado de `$next($request)`. No hay un tercer caso.
 *
 * Concretamente comprueba dos cosas:
 *  1. Un middleware que SÍ sigue el contrato (llama a $next y hace
 *     `return`) deja pasar la petición hasta el siguiente middleware
 *     de la cadena, y ese siguiente middleware puede bloquearla.
 *  2. Un middleware que rompe el contrato (llama a $next() sin
 *     `return`, devolviendo null implícitamente) hace fallar la
 *     petición de forma explícita (TypeError -> 500), en vez de
 *     saltarse en silencio el resto del pipeline. Antes de la
 *     corrección, este segundo caso devolvía 200 con el contenido
 *     protegido: el middleware "csrf_strict" nunca llegaba a
 *     ejecutarse.
 *
 * No requiere PHPUnit ni base de datos. Uso:
 *   php tests/router_middleware_pipeline_test.php
 */

require __DIR__ . '/../framework/Http/Request.php';
require __DIR__ . '/../framework/Http/Response.php';
require __DIR__ . '/../framework/Exceptions/HttpException.php';
require __DIR__ . '/../framework/Exceptions/NotFoundException.php';
require __DIR__ . '/../framework/Routing/Route.php';
require __DIR__ . '/../framework/Routing/Router.php';

use Misi\Http\Request;
use Misi\Http\Response;
use Misi\Routing\Router;

$failures = 0;

function check(string $label, bool $condition, int &$failures): void
{
    if ($condition) {
        echo "  OK   {$label}\n";
        return;
    }

    echo "  FAIL {$label}\n";
    $failures++;
}

function makeRequest(string $method, string $uri): Request
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $uri;

    return Request::capture();
}

echo "Caso 1: middleware que respeta el contrato (return \$next(\$request))\n";
$router = new Router();
$router->aliasMiddleware('auth_ok', function (Request $request, callable $next) {
    return $next($request);
});
$router->aliasMiddleware('csrf_block', function (Request $request, callable $next) {
    return new Response('BLOCKED BY CSRF', 419);
});
$router->post('/secret-1', function (Request $request) {
    return new Response('SECRET DATA', 200);
}, ['auth_ok', 'csrf_block']);

$response = $router->dispatch(makeRequest('POST', '/secret-1'));
check('csrf_block sigue en la cadena y bloquea con 419', $response->getStatusCode() === 419, $failures);

echo "\nCaso 2: middleware que rompe el contrato (olvida el 'return')\n";
$router2 = new Router();
$router2->aliasMiddleware('auth_buggy', function (Request $request, callable $next) {
    $next($request); // bug intencional: falta "return"
});
$router2->aliasMiddleware('csrf_block', function (Request $request, callable $next) {
    return new Response('BLOCKED BY CSRF', 419);
});
$router2->post('/secret-2', function (Request $request) {
    return new Response('SECRET DATA', 200);
}, ['auth_buggy', 'csrf_block']);

$threw = false;
try {
    $router2->dispatch(makeRequest('POST', '/secret-2'));
} catch (\TypeError $e) {
    $threw = true;
}
check('middleware roto falla ruidoso (TypeError) en vez de saltarse csrf_block', $threw, $failures);

echo "\n";

if ($failures > 0) {
    echo "{$failures} verificación(es) fallaron.\n";
    exit(1);
}

echo "Todas las verificaciones pasaron.\n";
exit(0);
