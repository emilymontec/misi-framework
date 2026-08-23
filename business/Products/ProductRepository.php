<?php

declare(strict_types=1);

namespace Misi\Business\Products;

use Misi\Database\Database;
use Misi\Exceptions\NotFoundException;

/**
 * Business Core (Fase 16/17). Stock simple (un número por producto, sin
 * variantes) — ver docs/business-core.md para por qué. `all()` trae el
 * nombre de categoría con un LEFT JOIN a propósito: es lo que cualquier
 * panel de administración de catálogo va a necesitar para listar
 * productos, igual que OrderController::index() en examples/demo-app ya
 * hace un JOIN equivalente con customers por la misma razón (evitar
 * N+1 en la vista que lo consume).
 */
final class ProductRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return $this->db->select(
            'SELECT products.*, categories.name AS category_name
             FROM products
             LEFT JOIN categories ON categories.id = products.category_id
             ORDER BY products.name ASC'
        );
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM products WHERE id = ?', [$id]);
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id): array
    {
        $product = $this->find($id);

        if ($product === null) {
            throw new NotFoundException('Producto no encontrado.');
        }

        return $product;
    }

    /** @param array<string, mixed> $data ya validado */
    public function create(array $data): string
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('products', [
            'category_id' => $data['category_id'] ?? null,
            'sku' => $data['sku'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** @param array<string, mixed> $data ya validado (sin stock_quantity — ver adjustStock()) */
    public function update(int $id, array $data): void
    {
        $this->db->update('products', [
            'category_id' => $data['category_id'] ?? null,
            'sku' => $data['sku'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public function delete(int $id): void
    {
        $this->db->delete('products', 'id = ?', [$id]);
    }

    /**
     * Ajusta el stock de forma atómica: `$delta` positivo para entradas
     * de inventario, negativo para salidas/ventas. Un solo UPDATE
     * condicionado (no leer-luego-escribir) para que dos ajustes
     * concurrentes no se pisen entre sí y para que nunca quede en
     * negativo — la condición `stock_quantity + ? >= 0` se evalúa en el
     * mismo UPDATE.
     */
    public function adjustStock(int $id, int $delta): void
    {
        $statement = $this->db->query(
            'UPDATE products
             SET stock_quantity = stock_quantity + ?, updated_at = ?
             WHERE id = ? AND stock_quantity + ? >= 0',
            [$delta, date('Y-m-d H:i:s'), $id, $delta]
        );

        if ($statement->rowCount() === 1) {
            return;
        }

        // rowCount 0: o el producto no existe, o el ajuste lo dejaría
        // en negativo. findOrFail() distingue el primer caso (lanza
        // NotFoundException); si el producto sí existe, es lo segundo.
        $product = $this->findOrFail($id);

        throw new InsufficientStockException($id, (int) $product['stock_quantity'], $delta);
    }

    /** @return array<string, array<int, string>> */
    public function rulesForCreate(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric'],
            'stock_quantity' => ['nullable', 'integer'],
        ];
    }

    /** @return array<string, array<int, string>> */
    public function rulesForUpdate(int $id): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', "unique:products,sku,{$id},id"],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric'],
        ];
    }
}
