<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Misi\Auth\Auth;
use Misi\Http\JsonResponse;
use Misi\Http\Request;

final class SessionController
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

        $user = Auth::user();
        $user['can_manage_orders'] = Auth::can('orders.manage');

        return JsonResponse::success($user, 'Sesión iniciada');
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        return JsonResponse::success(null, 'Sesión cerrada');
    }

    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user['can_manage_orders'] = Auth::can('orders.manage');

        return JsonResponse::success($user);
    }
}
