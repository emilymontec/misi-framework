<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Misi\Auth\Auth;
use Misi\Exceptions\AuthorizationException;
use Misi\Exceptions\NotFoundException;
use Misi\Http\JsonResponse;
use Misi\Http\Request;
use Misi\Http\Response;

/**
 * CRUD de pedidos, con imagen de referencia opcional (Storage, Fase 8) y
 * autorización explícita para borrar (Auth::can(), Fase 6.1) — el Router
 * no soporta middleware con parámetros ('can:orders.manage'), así que la
 * verificación se hace directo en el controlador, como recomienda
 * docs/authorization.md del proyecto padre.
 */
final class OrderController
{
    public function index(Request $request): JsonResponse
    {
        $orders = app()->database()->select(
            'SELECT orders.*, customers.name AS customer_name
             FROM orders
             INNER JOIN customers ON customers.id = orders.customer_id
             ORDER BY orders.created_at DESC'
        );

        foreach ($orders as &$order) {
            $order['reference_image_url'] = $order['reference_image'] !== null
                ? app()->storage()->url($order['reference_image'])
                : null;
        }

        return JsonResponse::success($orders);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $order = $this->findOrFail($id);
        $order['reference_image_url'] = $order['reference_image'] !== null
            ? app()->storage()->url($order['reference_image'])
            : null;

        return JsonResponse::success($order);
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->all();
        $input['reference_image'] = $request->file('reference_image');

        $data = app()->validator()->validate($input, [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'description' => ['required', 'string', 'max:255'],
            'reference_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max_size:2048'],
        ]);

        $path = isset($data['reference_image'])
            ? app()->storage()->putUploadedFile($data['reference_image'], 'orders')
            : null;

        $now = date('Y-m-d H:i:s');
        $id = app()->database()->insert('orders', [
            'customer_id' => $data['customer_id'],
            'description' => $data['description'],
            'status' => 'pendiente',
            'reference_image' => $path,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return JsonResponse::success(['id' => $id], 'Pedido creado', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->findOrFail($id);

        $data = app()->validator()->validate($request->all(), [
            'description' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:pendiente,en_proceso,listo,entregado'],
        ]);

        app()->database()->update('orders', [
            ...$data,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        return JsonResponse::success(null, 'Pedido actualizado');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (!Auth::can('orders.manage')) {
            throw new AuthorizationException('No tienes permiso para eliminar pedidos.');
        }

        $order = $this->findOrFail($id);

        if ($order['reference_image'] !== null) {
            app()->storage()->delete($order['reference_image']);
        }

        app()->database()->delete('orders', 'id = ?', [$id]);

        return JsonResponse::success(null, 'Pedido eliminado');
    }

    /**
     * Sirve la imagen de referencia de un pedido. Protegida con 'auth'
     * en routes/web.php — a diferencia de la demo pública del proyecto
     * padre (Fase 8), aquí sí tiene sentido exigir sesión: son fotos de
     * pedidos de clientes, no avatares públicos.
     */
    public function showAttachment(Request $request, string $path): Response
    {
        $order = app()->database()->selectOne('SELECT id FROM orders WHERE reference_image = ?', [$path]);

        if ($order === null) {
            // No se revela si el archivo existe en disco o no: si no
            // corresponde a un pedido real, es un 404 sin más detalle.
            throw new NotFoundException('Archivo no encontrado.');
        }

        $storage = app()->storage();

        return (new Response($storage->get($path)))
            ->header('Content-Type', $storage->mimeType($path));
    }

    /** @return array<string, mixed> */
    private function findOrFail(string $id): array
    {
        $order = app()->database()->selectOne('SELECT * FROM orders WHERE id = ?', [$id]);

        if ($order === null) {
            throw new NotFoundException('Pedido no encontrado.');
        }

        return $order;
    }
}
