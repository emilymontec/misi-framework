<?php

declare(strict_types=1);

use Misi\Http\JsonResponse;
use Misi\Http\Response;

/**
 * Rutas iniciales de un proyecto Misi nuevo.
 *
 * Sin controladores de por medio a propósito: un proyecto recién creado
 * con `misi new` no trae ningún código bajo backend/app/ todavía (ver
 * "misi make:controller" para generar el primero). "/" sirve
 * directamente el index.html de la raíz del proyecto — el mismo
 * archivo que Apache serviría sin pasar por PHP en producción (ver
 * .htaccess raíz), para que el dev local (`misi serve`) muestre
 * exactamente lo mismo.
 *
 * Agrega tus propias rutas aquí para lo que no pertenezca a un módulo
 * concreto, o mejor aún, dentro de un módulo (`misi make:module Nombre`)
 * si el proyecto crece — ver docs del framework sobre módulos.
 *
 * @var \Misi\Routing\Router $router
 */

$router->get('/', function () {
    $html = @file_get_contents(dirname(__DIR__) . '/index.html');

    return new Response($html !== false ? $html : 'Tu proyecto Misi está listo.');
});

$router->get('/api/ping', fn () => JsonResponse::success([
    'pong' => true,
    'timestamp' => date(DATE_ATOM),
], 'Tu proyecto Misi responde correctamente'));
