<?php

declare(strict_types=1);

namespace Misi\Database;

/**
 * Ejecuta, revierte y reporta el estado de las migraciones ubicadas en
 * database/migrations/ y, desde la Fase 10, también en las carpetas
 * migrations/ de cada módulo registrado (modules/NombreModulo/migrations/).
 *
 * Convención de nombres: 001_create_users.php, 002_create_businesses.php...
 * El prefijo numérico determina el orden de ejecución DENTRO de cada
 * fuente. Entre fuentes, el orden es: core primero, luego módulos en el
 * orden en que Application los descubrió (alfabético por carpeta).
 *
 * Identificador de migración guardado en la tabla `migrations`:
 *  - Core (database/migrations/): el nombre de archivo tal cual
 *    ("001_create_users_table.php") — se mantiene así por compatibilidad
 *    con proyectos que ya tienen migraciones de core registradas antes
 *    de la Fase 10.
 *  - Módulo: "NombreModulo/001_create_x.php" — el prefijo evita
 *    colisiones si dos módulos numeran sus migraciones igual.
 *
 * No implementa: migraciones en paralelo, dependencias explícitas entre
 * migraciones más allá del orden por nombre, ni un DSL de esquema (ver
 * Migration.php). Si un proyecto real necesita algo más sofisticado, se
 * evalúa entonces (regla de oro de abstracciones).
 */
final class Migrator
{
    /** @param array<int, array{label: string|null, path: string}> $sources */
    public function __construct(
        private readonly Database $db,
        private readonly array $sources
    ) {
    }

    public function ensureMigrationsTable(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT UNSIGNED NOT NULL,
                run_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    /**
     * Mapa identificador -> ruta completa del archivo, para TODAS las
     * fuentes combinadas (core + módulos), en orden.
     *
     * @return array<string, string>
     */
    private function allMigrationFiles(): array
    {
        $identifiers = [];

        foreach ($this->sources as $source) {
            $files = glob(rtrim($source['path'], '/') . '/*.php') ?: [];
            sort($files);

            foreach ($files as $fullPath) {
                $name = basename($fullPath);
                $identifier = $source['label'] !== null ? "{$source['label']}/{$name}" : $name;
                $identifiers[$identifier] = $fullPath;
            }
        }

        return $identifiers;
    }

    /** @return array<int, string> */
    private function ranMigrations(): array
    {
        $this->ensureMigrationsTable();

        return array_column(
            $this->db->select('SELECT migration FROM migrations ORDER BY id ASC'),
            'migration'
        );
    }

    /** @return array<int, string> identificadores pendientes, en orden */
    public function pending(): array
    {
        $ran = $this->ranMigrations();
        $all = array_keys($this->allMigrationFiles());

        return array_values(array_diff($all, $ran));
    }

    private function nextBatchNumber(): int
    {
        $row = $this->db->selectOne('SELECT MAX(batch) AS max_batch FROM migrations');
        $max = $row['max_batch'] ?? 0;

        return ((int) $max) + 1;
    }

    private function loadMigration(string $identifier, string $fullPath): Migration
    {
        $migration = require $fullPath;

        if (!$migration instanceof Migration) {
            throw new \RuntimeException("La migración {$identifier} debe retornar una instancia de Migration.");
        }

        $migration->setDatabase($this->db);

        return $migration;
    }

    /**
     * Ejecuta todas las migraciones pendientes.
     *
     * @return array<int, string> identificadores de las migraciones ejecutadas
     */
    public function run(): array
    {
        $files = $this->allMigrationFiles();
        $pending = $this->pending();

        if ($pending === []) {
            return [];
        }

        $batch = $this->nextBatchNumber();
        $executed = [];

        foreach ($pending as $identifier) {
            $migration = $this->loadMigration($identifier, $files[$identifier]);
            $migration->up();

            $this->db->insert('migrations', [
                'migration' => $identifier,
                'batch' => $batch,
                'run_at' => date('Y-m-d H:i:s'),
            ]);

            $executed[] = $identifier;
        }

        return $executed;
    }

    /**
     * Revierte el último lote (batch) de migraciones ejecutadas.
     *
     * @return array<int, string> identificadores de las migraciones revertidas
     */
    public function rollback(): array
    {
        $this->ensureMigrationsTable();

        $row = $this->db->selectOne('SELECT MAX(batch) AS max_batch FROM migrations');
        $batch = $row['max_batch'] ?? null;

        if ($batch === null) {
            return [];
        }

        $rows = $this->db->select(
            'SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC',
            [$batch]
        );

        $files = $this->allMigrationFiles();
        $reverted = [];

        foreach ($rows as $row) {
            $identifier = $row['migration'];

            if (!isset($files[$identifier])) {
                // El archivo ya no existe en disco (módulo removido, etc.)
                // — se limpia el registro igual, sin poder ejecutar down().
                $this->db->delete('migrations', 'migration = ?', [$identifier]);
                $reverted[] = $identifier;
                continue;
            }

            $migration = $this->loadMigration($identifier, $files[$identifier]);
            $migration->down();

            $this->db->delete('migrations', 'migration = ?', [$identifier]);

            $reverted[] = $identifier;
        }

        return $reverted;
    }

    /**
     * @return array<int, array{migration: string, status: string, batch: int|null}>
     */
    public function status(): array
    {
        $this->ensureMigrationsTable();

        $ranRows = $this->db->select('SELECT migration, batch FROM migrations');
        $ranMap = [];
        foreach ($ranRows as $row) {
            $ranMap[$row['migration']] = (int) $row['batch'];
        }

        $result = [];
        foreach (array_keys($this->allMigrationFiles()) as $identifier) {
            $result[] = [
                'migration' => $identifier,
                'status' => isset($ranMap[$identifier]) ? 'Ran' : 'Pending',
                'batch' => $ranMap[$identifier] ?? null,
            ];
        }

        return $result;
    }
}
