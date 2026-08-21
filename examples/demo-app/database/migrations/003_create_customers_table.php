<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * Demo (Fase 14): clientes del taller.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS customers (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                phone VARCHAR(30) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS customers');
    }
};
