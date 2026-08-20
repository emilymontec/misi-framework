<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * Migración de ejemplo (Fase 8): demuestra el patrón "la base de datos
 * solo guarda metadata, el archivo vive en Storage". No es un modelo de
 * negocio fijo — cada proyecto adapta esta tabla o crea la suya propia
 * (ej. como parte de un futuro Business Core, ver ROADMAP Fase 16).
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS uploads (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                path VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                mime_type VARCHAR(150) NOT NULL,
                size INT UNSIGNED NOT NULL,
                uploaded_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS uploads');
    }
};
