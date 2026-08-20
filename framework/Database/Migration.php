<?php

declare(strict_types=1);

namespace Misi\Database;

/**
 * Clase base para una migración individual.
 *
 * Una migración es un archivo PHP en database/migrations/ que devuelve
 * una instancia anónima de esta clase, con up() para aplicar el cambio
 * y down() para revertirlo.
 *
 * Ejemplo (database/migrations/001_create_users.php):
 *
 *   <?php
 *   use Misi\Database\Migration;
 *
 *   return new class extends Migration {
 *       public function up(): void
 *       {
 *           $this->db->query("CREATE TABLE users (...)");
 *       }
 *
 *       public function down(): void
 *       {
 *           $this->db->query("DROP TABLE IF EXISTS users");
 *       }
 *   };
 *
 * No se implementa un DSL de definición de esquema (Schema::create(...)):
 * SQL crudo mantiene esto simple y sigue siendo legible para una
 * desarrolladora junior/intermedia (ver docs/architecture.md, sección 8).
 */
abstract class Migration
{
    public Database $db;

    /** Inyectado por el Migrator antes de llamar up()/down(). */
    public function setDatabase(Database $db): void
    {
        $this->db = $db;
    }

    abstract public function up(): void;

    abstract public function down(): void;
}
