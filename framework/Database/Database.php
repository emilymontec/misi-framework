<?php

declare(strict_types=1);

namespace Misi\Database;

use PDO;
use PDOException;
use PDOStatement;
use Misi\Exceptions\DatabaseException;

/**
 * Wrapper delgado sobre PDO.
 *
 * Responsabilidades: conexión reutilizada, prepared statements,
 * transacciones. NO es un ORM ni un Query Builder (ver ROADMAP Fase 4.1
 * para un Query Builder mínimo opcional, solo si se justifica).
 *
 * Todas las consultas se ejecutan con sentencias preparadas: nunca
 * se concatena input del usuario dentro de SQL.
 */
final class Database
{
    private ?PDO $connection = null;

    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config)
    {
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $host = $this->config['host'] ?? '127.0.0.1';
        $port = $this->config['port'] ?? 3306;
        $database = $this->config['database'] ?? '';
        $charset = $this->config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        try {
            $this->connection = new PDO(
                $dsn,
                (string) ($this->config['username'] ?? 'root'),
                (string) ($this->config['password'] ?? ''),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            // DatabaseException (no RuntimeException genérica, desde
            // Fase 9): Application::handleException() ya sabe traducirla
            // a un 500 JSON seguro y registrarla en el log, conservando
            // el PDOException original vía getPrevious() para el detalle.
            throw new DatabaseException('No fue posible conectar a la base de datos.', 500, $e);
        }

        return $this->connection;
    }

    /** @param array<int|string, mixed> $bindings */
    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $statement = $this->connection()->prepare($sql);
        $statement->execute($bindings);
        return $statement;
    }

    /** @param array<int|string, mixed> $bindings
     *  @return array<string, mixed>|null
     */
    public function selectOne(string $sql, array $bindings = []): ?array
    {
        $row = $this->query($sql, $bindings)->fetch();
        return $row === false ? null : $row;
    }

    /** @param array<int|string, mixed> $bindings
     *  @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll();
    }

    /** @param array<string, mixed> $data */
    public function insert(string $table, array $data): string
    {
        $this->assertSafeIdentifier($table);
        array_map($this->assertSafeIdentifier(...), array_keys($data));

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));

        return $this->connection()->lastInsertId();
    }

    /** @param array<string, mixed> $data
     *  @param array<int|string, mixed> $whereBindings
     */
    public function update(string $table, array $data, string $where, array $whereBindings = []): int
    {
        $this->assertSafeIdentifier($table);
        array_map($this->assertSafeIdentifier(...), array_keys($data));

        $set = implode(', ', array_map(fn ($col) => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        $statement = $this->query($sql, [...array_values($data), ...$whereBindings]);

        return $statement->rowCount();
    }

    /** @param array<int|string, mixed> $whereBindings */
    public function delete(string $table, string $where, array $whereBindings = []): int
    {
        $this->assertSafeIdentifier($table);

        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $whereBindings)->rowCount();
    }

    /**
     * Fase 15 (auditoría de seguridad): $table y las claves de $data en
     * insert()/update() se interpolan directamente en el SQL (no pueden
     * ir como binding preparado — PDO no soporta parametrizar
     * identificadores, solo valores). Esto es seguro mientras el nombre
     * de tabla/columna lo escribe la desarrolladora en su propio código
     * (el caso normal). Esta validación es una segunda barrera para el
     * caso en que, por error, alguien construya $data a partir de input
     * no filtrado (ej. `$db->insert('customers', $request->all())` sin
     * pasar antes por Validator) — un nombre de columna con SQL
     * incrustado no rompe la query, se rechaza con DatabaseException
     * antes de tocar la base de datos.
     *
     * `where` en update()/delete() queda fuera a propósito: ahí
     * siempre viaja SQL real (ej. "id = ? AND business_id = ?"), no un
     * identificador simple — no hay forma de "sanearlo" sin convertirlo
     * en un mini query builder, que es exactamente lo que Fase 4/19
     * decidió no construir todavía. `where` sigue siendo responsabilidad
     * de quien la escribe, igual que cualquier SQL crudo del proyecto.
     */
    private function assertSafeIdentifier(string $identifier): string
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier) !== 1) {
            throw new DatabaseException(
                "Nombre de tabla o columna inválido: '{$identifier}'."
            );
        }

        return $identifier;
    }

    public function beginTransaction(): void
    {
        $this->connection()->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection()->commit();
    }

    public function rollBack(): void
    {
        $this->connection()->rollBack();
    }

    /**
     * Ejecuta un callback dentro de una transacción.
     * Si el callback lanza una excepción, se hace rollback automáticamente.
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function lastInsertId(): string
    {
        return $this->connection()->lastInsertId();
    }
}
