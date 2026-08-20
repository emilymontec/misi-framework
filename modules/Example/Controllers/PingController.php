<?php

declare(strict_types=1);

namespace Modules\Example\Controllers;

use Misi\Http\JsonResponse;
use Misi\Http\Request;

/**
 * Controlador de ejemplo — demuestra que un módulo puede tener sus
 * propios controladores en su propio namespace (Modules\Example\...),
 * autoloaded igual que App\ y Misi\ (ver composer.json y
 * bootstrap/autoload.php), y usar las mismas herramientas del framework
 * (app()->database(), Validator, etc.) que cualquier controlador de app/.
 */
final class PingController
{
    public function __invoke(Request $request): JsonResponse
    {
        $count = app()->database()->selectOne(
            'SELECT COUNT(*) AS total FROM example_pings'
        )['total'] ?? 0;

        app()->database()->insert('example_pings', [
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return JsonResponse::success([
            'module' => 'Example',
            'pings_previos' => (int) $count,
        ], 'pong desde un módulo');
    }
}
