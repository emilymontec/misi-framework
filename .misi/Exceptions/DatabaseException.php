<?php

declare(strict_types=1);

namespace Misi\Exceptions;

use Throwable;

/**
 * Envuelve fallos de PDO (conexión o consulta) con un mensaje genérico
 * y seguro hacia el cliente, sin dejar de conservar el error original
 * (`getPrevious()`) para que Application lo registre en el log con
 * detalle — ver docs/logging.md.
 */
class DatabaseException extends HttpException
{
    public function __construct(
        string $message = 'Error de base de datos.',
        int $statusCode = 500,
        ?Throwable $previous = null
    ) {
        parent::__construct($statusCode, $message, $previous);
    }
}
