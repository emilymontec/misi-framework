<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * Migración del módulo Example. El Migrator la identifica como
 * "Example/001_create_example_pings_table.php" en la tabla `migrations`
 * (prefijo automático con el 'name' declarado en module.php) — así dos
 * módulos distintos pueden tener cada uno un "001_....php" sin chocar.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS example_pings (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                created_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS example_pings');
    }
};
