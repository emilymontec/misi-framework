<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * RBAC simple (Fase 6.1): roles y permisos, con dos tablas pivote.
 *
 * Convención de nombre de permiso: "recurso.accion" (ej. "orders.create",
 * "users.manage"). No hay jerarquía de roles ni permisos condicionales —
 * si un proyecto real necesita algo más granular, se evalúa entonces.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS roles (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                created_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $this->db->query(
            'CREATE TABLE IF NOT EXISTS permissions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL UNIQUE,
                created_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $this->db->query(
            'CREATE TABLE IF NOT EXISTS role_user (
                role_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (role_id, user_id),
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $this->db->query(
            'CREATE TABLE IF NOT EXISTS permission_role (
                permission_id INT UNSIGNED NOT NULL,
                role_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (permission_id, role_id),
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(): void
    {
        // Orden inverso: primero las tablas con foreign keys hacia las otras.
        $this->db->query('DROP TABLE IF EXISTS permission_role');
        $this->db->query('DROP TABLE IF EXISTS role_user');
        $this->db->query('DROP TABLE IF EXISTS permissions');
        $this->db->query('DROP TABLE IF EXISTS roles');
    }
};
