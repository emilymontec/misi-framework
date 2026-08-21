<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Misi\Exceptions\NotFoundException;
use Misi\Http\JsonResponse;
use Misi\Http\Request;

/**
 * CRUD completo de clientes. Controlador delgado: valida, delega en
 * Database directamente (sin capa de Repository — para un solo recurso
 * simple como este no se justifica todavía, ver la "regla de oro de
 * abstracciones" del proyecto padre).
 */
final class CustomerController
{
    public function index(Request $request): JsonResponse
    {
        $customers = app()->database()->select('SELECT * FROM customers ORDER BY name ASC');

        return JsonResponse::success($customers);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $customer = $this->findOrFail($id);

        return JsonResponse::success($customer);
    }

    public function store(Request $request): JsonResponse
    {
        $data = app()->validator()->validate($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $now = date('Y-m-d H:i:s');
        $id = app()->database()->insert('customers', [
            ...$data,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return JsonResponse::success(['id' => $id], 'Cliente creado', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->findOrFail($id);

        $data = app()->validator()->validate($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', "unique:customers,email,{$id},id"],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        app()->database()->update('customers', [
            ...$data,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        return JsonResponse::success(null, 'Cliente actualizado');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->findOrFail($id);

        // ON DELETE CASCADE en la migración de orders se encarga de sus pedidos.
        app()->database()->delete('customers', 'id = ?', [$id]);

        return JsonResponse::success(null, 'Cliente eliminado');
    }

    /** @return array<string, mixed> */
    private function findOrFail(string $id): array
    {
        $customer = app()->database()->selectOne('SELECT * FROM customers WHERE id = ?', [$id]);

        if ($customer === null) {
            throw new NotFoundException('Cliente no encontrado.');
        }

        return $customer;
    }
}
