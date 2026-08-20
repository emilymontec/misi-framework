<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Misi\Http\JsonResponse;
use Misi\Http\Request;

/**
 * Controlador de demostración del sistema de Validation (Fase 5).
 * No es parte del framework: vive en app/ como ejemplo de uso.
 */
final class ValidationDemoController
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = app()->validator()->validate($request->all(), [
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'unique:users,email'],
            'age' => ['nullable', 'integer', 'min:18', 'max:120'],
        ]);

        return JsonResponse::success($data, 'Datos válidos');
    }
}
