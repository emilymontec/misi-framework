<?php

declare(strict_types=1);

namespace Misi\Business\Customers;

use Misi\Database\Database;
use Misi\Exceptions\NotFoundException;

/**
 * Business Core (Fase 16) — primera pieza.
 *
 * Por qué existe: es literalmente el mismo código que ya vivía en
 * examples/demo-app/app/Http/Controllers/CustomerController.php
 * (nombre, email, teléfono — CRUD estándar), y que el segundo proyecto
 * real (tienda/retail, en planificación) también necesita tal cual, sin
 * ningún campo específico de negocio. Es el caso de uso exacto que
 * justifica moverlo a una capa reutilizable, según la "regla de oro de
 * abstracciones" de ROADMAP.md: hay 2 proyectos reales que lo necesitan,
 * con la MISMA forma, no una generalización especulativa.
 *
 * Qué NO es esta clase: no reemplaza Repository/Service para recursos
 * específicos de cada negocio (Orders, Products...) — ver
 * docs/business-core.md para qué entró en este primer corte y qué se
 * dejó deliberadamente afuera, y por qué.
 *
 * Cómo se usa en un proyecto: se copia la carpeta `business/` (igual que
 * `.misi/`) junto al proyecto, y el controlador del proyecto
 * instancia esta clase directamente — Business Core NO se registra en
 * `Application` (el framework no debe saber qué es un cliente, ver
 * ROADMAP.md sección 36).
 *
 *   $customers = new CustomerRepository(app()->database());
 *   $customers->create($data);
 */
final class CustomerRepository
{
    public function __construct(private readonly Database $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return $this->db->select('SELECT * FROM customers ORDER BY name ASC');
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM customers WHERE id = ?', [$id]);
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id): array
    {
        $customer = $this->find($id);

        if ($customer === null) {
            throw new NotFoundException('Cliente no encontrado.');
        }

        return $customer;
    }

    /**
     * @param array<string, mixed> $data ya validado (ver rulesForCreate()/rulesForUpdate())
     */
    public function create(array $data): string
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->insert('customers', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** @param array<string, mixed> $data ya validado */
    public function update(int $id, array $data): void
    {
        $this->db->update('customers', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }

    public function delete(int $id): void
    {
        $this->db->delete('customers', 'id = ?', [$id]);
    }

    /**
     * Reglas de Validator (Fase 5) para crear un cliente. Un proyecto
     * puede extenderlas (ej. agregar un campo propio) fusionando el
     * array devuelto, en vez de reescribirlas desde cero.
     *
     * @return array<string, array<int, string>>
     */
    public function rulesForCreate(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    /** @return array<string, array<int, string>> */
    public function rulesForUpdate(int $id): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', "unique:customers,email,{$id},id"],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
