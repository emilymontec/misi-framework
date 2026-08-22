<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * Business Core (Fase 16) — misma forma exacta que
 * examples/demo-app/database/migrations/003_create_customers_table.php,
 * movida acá porque es la parte que se repite igual en el segundo
 * proyecto real (retail). Un proyecto que copia business/ a su carpeta
 * corre esto con `php bin/biz migrate` — se descubre solo (ver
 * `buildMigrator()` en bin/biz).
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS customers (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL,
                phone VARCHAR(30) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uniq_customers_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS customers');
    }
};
