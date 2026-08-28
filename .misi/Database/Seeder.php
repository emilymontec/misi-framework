<?php

declare(strict_types=1);

namespace Misi\Database;

/**
 * Clase base para un seeder.
 *
 * Un seeder inserta datos iniciales (usuario admin, configuración base,
 * datos demo). Cada proyecto define sus propios seeders en
 * database/seeders/, extendiendo esta clase.
 *
 * No se implementa un sistema de "factories" con datos falsos aleatorios
 * (tipo Faker) desde el inicio: se agrega solo si un proyecto real lo
 * necesita para poblar datos de demostración de forma masiva.
 */
abstract class Seeder
{
    public Database $db;

    /** Inyectado por el runner antes de llamar run(). */
    public function setDatabase(Database $db): void
    {
        $this->db = $db;
    }

    abstract public function run(): void;

    /** Atajo para no repetir $this->db->insert(...) en cada seeder. */
    protected function insert(string $table, array $data): string
    {
        return $this->db->insert($table, $data);
    }
}
