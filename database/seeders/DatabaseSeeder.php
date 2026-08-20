<?php

declare(strict_types=1);

use Misi\Database\Seeder;

/**
 * Seeder raíz. Se ejecuta con `php bin/biz db:seed`.
 *
 * Aquí se orquestan los demás seeders del proyecto. Este ejemplo crea un
 * usuario administrador demo (con contraseña hasheada, nunca en texto
 * plano) y le asigna un rol "admin" con un permiso "users.manage", para
 * que tanto el login (Fase 6) como Auth::can() (Fase 6.1) tengan con qué
 * probarse.
 */
return new class extends Seeder {
    public function run(): void
    {
        $userId = $this->seedAdminUser();
        $this->seedRolesAndPermissions($userId);
    }

    private function seedAdminUser(): string
    {
        $existing = $this->db->selectOne(
            'SELECT id FROM users WHERE email = ?',
            ['admin@misi.test']
        );

        if ($existing !== null) {
            echo "  - Usuario admin demo ya existe, se omite.\n";
            return (string) $existing['id'];
        }

        $id = $this->insert('users', [
            'name' => 'Administrador',
            'email' => 'admin@misi.test',
            'password' => password_hash('changeme', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        echo "  - Usuario admin demo creado (admin@misi.test / changeme).\n";

        return $id;
    }

    private function seedRolesAndPermissions(string $userId): void
    {
        $roleId = $this->firstOrCreate('roles', ['name' => 'admin'], [
            'name' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $permissionId = $this->firstOrCreate('permissions', ['name' => 'users.manage'], [
            'name' => 'users.manage',
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

        echo "  - Rol 'admin' con permiso 'users.manage' asignado al admin demo.\n";
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

