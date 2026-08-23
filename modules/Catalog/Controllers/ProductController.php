<?php

declare(strict_types=1);

namespace Modules\Catalog\Controllers;

use Misi\Auth\Auth;
use Misi\Business\Products\ProductRepository;
use Misi\Exceptions\AuthorizationException;
use Misi\Http\JsonResponse;
use Misi\Http\Request;

/**
 * CRUD de productos + ajuste de stock. Dos permisos separados a
 * propósito: 'products.manage' (crear/editar/borrar el catálogo) e
 * 'inventory.manage' (ajustar cantidades) — en una tienda real suele
 * ser gente distinta la que da de alta un producto y la que hace
 * conteos/ajustes de inventario. Un proyecto puede asignar ambos al
 * mismo rol si no necesita esa separación.
 */
final class ProductController
{
    private ProductRepository $products;

    public function __construct()
    {
        $this->products = new ProductRepository(app()->database());
    }

    public function index(Request $request): JsonResponse
    {
        return JsonResponse::success($this->products->all());
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return JsonResponse::success($this->products->findOrFail((int) $id));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('products.manage', 'administrar el catálogo');

        $data = app()->validator()->validate($request->all(), $this->products->rulesForCreate());
        $id = $this->products->create($data);

        return JsonResponse::success(['id' => $id], 'Producto creado', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->authorize('products.manage', 'administrar el catálogo');

        $this->products->findOrFail((int) $id);
        $data = app()->validator()->validate($request->all(), $this->products->rulesForUpdate((int) $id));
        $this->products->update((int) $id, $data);

        return JsonResponse::success(null, 'Producto actualizado');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->authorize('products.manage', 'administrar el catálogo');

        $this->products->findOrFail((int) $id);
        $this->products->delete((int) $id);

        return JsonResponse::success(null, 'Producto eliminado');
    }

    /**
     * Entradas (`delta` positivo) y salidas (`delta` negativo) de
     * inventario. ProductRepository::adjustStock() ya es atómico y
     * rechaza dejar el stock en negativo (InsufficientStockException,
     * 422 — Application::handleException() la maneja igual que
     * cualquier otra excepción de Fase 9, sin caso especial acá).
     */
    public function adjustStock(Request $request, string $id): JsonResponse
    {
        $this->authorize('inventory.manage', 'ajustar inventario');

        $data = app()->validator()->validate($request->all(), [
            'delta' => ['required', 'integer'],
        ]);

        $this->products->adjustStock((int) $id, (int) $data['delta']);

        return JsonResponse::success($this->products->findOrFail((int) $id), 'Stock actualizado');
    }

    private function authorize(string $permission, string $accion): void
    {
        if (!Auth::can($permission)) {
            throw new AuthorizationException("No tienes permiso para {$accion}.");
        }
    }
}
