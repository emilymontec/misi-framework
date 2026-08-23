<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * Business Core (Fase 16/17). Genérica a propósito: solo nombre —
 * nada específico de un tipo de producto (eso es lo que la diferencia
 * de un "Modules\Ropa" con tallas/colores, que sigue sin construirse
 * por falta de evidencia real, ver ROADMAP.md Fase 17).
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uniq_categories_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS categories');
    }
};
