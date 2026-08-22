<?php

declare(strict_types=1);

use Misi\Database\Migration;

/**
 * Fase 15 (revisión de rendimiento): Auth::can() filtra role_user por
 * user_id en cada verificación de permiso ("WHERE ru.user_id = ?" en
 * framework/Auth/Auth.php). La única clave existente en role_user es la
 * PRIMARY KEY compuesta (role_id, user_id) — por la regla de prefijo
 * izquierdo de InnoDB, esa clave NO sirve para filtrar solo por user_id,
 * así que esa consulta hacía un table scan completo de role_user en
 * cada permiso verificado.
 *
 * Migración aditiva (no se modifica 002_create_roles_and_permissions.php
 * directamente): un proyecto que ya corrió esa migración en producción
 * solo necesita correr esta nueva, igual que cualquier otro cambio de
 * esquema posterior al deploy inicial.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->db->query(
            'CREATE INDEX idx_role_user_user_id ON role_user (user_id)'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'DROP INDEX idx_role_user_user_id ON role_user'
        );
    }
};
