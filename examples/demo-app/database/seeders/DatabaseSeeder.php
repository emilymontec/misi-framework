<?php

declare(strict_types=1);

use Misi\Database\Seeder;

/**
 * Seeder de la demo (Fase 14). Crea un usuario de staff, un rol con
 * permiso sobre pedidos, y algunos clientes/pedidos de ejemplo para que
 * la interfaz no se vea vacía al abrirla por primera vez.
 *
 * Igual que el seeder del proyecto padre: usa firstOrCreate para poder
 * correrse más de una vez sin duplicar datos.
 */
return new class extends Seeder {
    public function run(): void
    {
        $userId = $this->seedStaffUser();
        $this->seedRoleAndPermission($userId);
        $customerIds = $this->seedCustomers();
        $this->seedOrders($customerIds);
    }

    private function seedStaffUser(): string
    {
        $existing = $this->db->selectOne('SELECT id FROM users WHERE email = ?', ['staff@bordados.test']);

        if ($existing !== null) {
            echo "  - Usuario de staff ya existe, se omite.\n";
            return (string) $existing['id'];
        }

        $id = $this->insert('users', [
            'name' => 'Ana (staff)',
            'email' => 'staff@bordados.test',
            'password' => password_hash('changeme', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        echo "  - Usuario de staff creado (staff@bordados.test / changeme).\n";

        return $id;
    }

    private function seedRoleAndPermission(string $userId): void
    {
        $roleId = $this->firstOrCreate('roles', ['name' => 'staff'], [
            'name' => 'staff',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $permissionId = $this->firstOrCreate('permissions', ['name' => 'orders.manage'], [
            'name' => 'orders.manage',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->query(
            'INSERT IGNORE INTO permission_role (permission_id, role_id) VALUES (?, ?)',
            [$permissionId, $roleId]
        );

        $this->db->query(
            'INSERT IGNORE INTO role_user (role_id, user_id) VALUES (?, ?)',
            [$roleId, $userId]
        );

        echo "  - Rol 'staff' con permiso 'orders.manage' asignado.\n";
    }

    /** @return array<int, string> IDs de los clientes (existentes o creados) */
    private function seedCustomers(): array
    {
        $demo = [
            ['name' => 'María Gómez', 'email' => 'maria@example.com', 'phone' => '555-0101'],
            ['name' => 'Carlos Ruiz', 'email' => 'carlos@example.com', 'phone' => '555-0102'],
        ];

        $ids = [];

        foreach ($demo as $customer) {
            $ids[] = $this->firstOrCreate('customers', ['email' => $customer['email']], [
                ...$customer,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo '  - ' . count($ids) . " clientes demo listos.\n";

        return $ids;
    }

    /** @param array<int, string> $customerIds */
    private function seedOrders(array $customerIds): void
    {
        $existing = $this->db->selectOne('SELECT id FROM orders LIMIT 1');

        if ($existing !== null) {
            echo "  - Pedidos demo ya existen, se omite.\n";
            return;
        }

        $this->insert('orders', [
            'customer_id' => $customerIds[0],
            'description' => 'Logo bordado en 3 gorras',
            'status' => 'en_proceso',
            'reference_image' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->insert('orders', [
            'customer_id' => $customerIds[1],
            'description' => 'Chaqueta con nombre bordado',
            'status' => 'pendiente',
            'reference_image' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        echo "  - 2 pedidos demo creados.\n";
    }

    /** @param array<string, mixed> $lookup
     *  @param array<string, mixed> $data
     */
    private function firstOrCreate(string $table, array $lookup, array $data): string
    {
        [$column, $value] = [array_key_first($lookup), reset($lookup)];

        $existing = $this->db->selectOne("SELECT id FROM {$table} WHERE {$column} = ?", [$value]);

        if ($existing !== null) {
            return (string) $existing['id'];
        }

        return $this->insert($table, $data);
    }
};
