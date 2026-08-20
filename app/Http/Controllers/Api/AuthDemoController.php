<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Misi\Auth\Auth;
use Misi\Http\JsonResponse;
use Misi\Http\Request;

/**
 * Controlador de demostración del sistema de Auth (Fase 6).
 * No es parte del framework: vive en app/ como ejemplo de uso.
 */
final class AuthDemoController
{
    public function login(Request $request): JsonResponse
    {
        $data = app()->validator()->validate($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($data['email'], $data['password'])) {
            return JsonResponse::error('Credenciales inválidas.', [], 401);
        }

        return JsonResponse::success(Auth::user(), 'Sesión iniciada');
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        return JsonResponse::success(null, 'Sesión cerrada');
    }

    /** Ruta protegida: solo accesible con el middleware 'auth'. */
    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user['can_manage_users'] = Auth::can('users.manage');

        return JsonResponse::success($user);
    }
}
