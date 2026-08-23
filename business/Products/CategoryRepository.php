<?php

declare(strict_types=1);

namespace Misi\Business\Products;

use Misi\Database\Database;
use Misi\Exceptions\NotFoundException;

/**
 * Business Core (Fase 16/17). Mismo patrón que
 * business/Customers/CustomerRepository.php — CRUD + reglas de
 * validación reutilizables, sin capa de Service (no se justifica
 * todavía para algo tan simple, misma regla de oro de siempre).
 */
final class CategoryRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return $this->db->select('SELECT * FROM categories ORDER BY name ASC');
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id): array
    {
        $category = $this->find($id);

        if ($category === null) {
            throw new NotFoundException('Categoría no encontrada.');
        }

        return $category;
    }

    /** @param array<string, mixed> $data ya validado */
    public function create(array $data): string
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('categories', [
            'name' => $data['name'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** @param array<string, mixed> $data ya validado */
    public function update(int $id, array $data): void
    {
        $this->db->update('categories', [
            'name' => $data['name'],
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public function delete(int $id): void
    {
        // No falla si tiene productos: la FK de products.category_id es
        // ON DELETE SET NULL (ver la migración) — los productos quedan
        // sin categorizar, no se bloquea ni se borran en cascada.
        $this->db->delete('categories', 'id = ?', [$id]);
    }

    /** @return array<string, array<int, string>> */
    public function rulesForCreate(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
        ];
    }

    /** @return array<string, array<int, string>> */
    public function rulesForUpdate(int $id): array
    {
        return [
            'name' => ['required', 'string', 'max:120', "unique:categories,name,{$id},id"],
        ];
    }
}
