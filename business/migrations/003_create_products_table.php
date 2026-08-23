<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * Business Core (Fase 16/17). Stock simple: una cantidad por producto,
 * sin variantes (talla/color) — decisión explícita para este primer
 * corte (ver docs/business-core.md). category_id es nullable con
 * ON DELETE SET NULL: borrar una categoría no debe borrar ni bloquear
 * el borrado de sus productos, solo los deja sin categorizar.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS products (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id INT UNSIGNED NULL,
                sku VARCHAR(50) NOT NULL,
                name VARCHAR(150) NOT NULL,
                description VARCHAR(500) NULL,
                price DECIMAL(10,2) NOT NULL,
                stock_quantity INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uniq_products_sku (sku),
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS products');
    }
};
