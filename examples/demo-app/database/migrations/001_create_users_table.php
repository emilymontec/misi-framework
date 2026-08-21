<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * Migración de ejemplo, incluida como demostración de que el sistema de
 * migraciones funciona de punta a punta.
 *
 * La tabla `users` aquí es la tabla de usuarios del sistema (para
 * autenticación, Fase 6), no un modelo de negocio. Cada proyecto puede
 * ajustar sus columnas libremente: esto es un punto de partida, no un
 * esquema fijo impuesto por el framework.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS users');
    }
};
