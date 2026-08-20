<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Misi\Http\JsonResponse;
use Misi\Http\Request;

final class PingController
{
    public function __invoke(Request $request): JsonResponse
    {
        return JsonResponse::success([
            'pong' => true,
            'timestamp' => date(DATE_ATOM),
        ], 'Misi API responde correctamente');
    }
}
