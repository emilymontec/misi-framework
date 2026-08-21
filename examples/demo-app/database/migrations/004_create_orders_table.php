<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * Demo (Fase 14): pedidos del taller. `reference_image` guarda solo la
 * ruta relativa que devuelve Storage — el archivo en sí vive en
 * storage/uploads/, nunca en la base de datos (ver docs/storage.md del
 * proyecto padre).
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS orders (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                customer_id INT UNSIGNED NOT NULL,
                description VARCHAR(255) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT "pendiente",
                reference_image VARCHAR(255) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS orders');
    }
};
