<?php

declare(strict_types=1);

namespace Modules\Catalog\Controllers;

use Misi\Auth\Auth;
use Misi\Business\Products\CategoryRepository;
use Misi\Exceptions\AuthorizationException;
use Misi\Http\JsonResponse;
use Misi\Http\Request;

/**
 * CRUD de categorías. Controlador delgado: valida y delega en
 * CategoryRepository (Business Core) — el propio módulo no sabe nada de
 * SQL. Permiso 'categories.manage' verificado inline (el Router no
 * soporta middleware con parámetros, ver docs/authorization.md).
 */
final class CategoryController
{
    private CategoryRepository $categories;

    public function __construct()
    {
        $this->categories = new CategoryRepository(app()->database());
    }

    public function index(Request $request): JsonResponse
    {
        return JsonResponse::success($this->categories->all());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage();

        $data = app()->validator()->validate($request->all(), $this->categories->rulesForCreate());
        $id = $this->categories->create($data);

        return JsonResponse::success(['id' => $id], 'Categoría creada', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->authorizeManage();

        $this->categories->findOrFail((int) $id);
        $data = app()->validator()->validate($request->all(), $this->categories->rulesForUpdate((int) $id));
        $this->categories->update((int) $id, $data);

        return JsonResponse::success(null, 'Categoría actualizada');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->authorizeManage();

        $this->categories->findOrFail((int) $id);
        $this->categories->delete((int) $id);

        return JsonResponse::success(null, 'Categoría eliminada');
    }

    private function authorizeManage(): void
    {
        if (!Auth::can('categories.manage')) {
            throw new AuthorizationException('No tienes permiso para administrar categorías.');
        }
    }
}
