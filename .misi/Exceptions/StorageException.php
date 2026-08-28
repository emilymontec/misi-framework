<?php

declare(strict_types=1);

namespace Misi\Exceptions;

/**
 * Errores de almacenamiento: archivo no encontrado, subida inválida,
 * fallo al escribir/mover un archivo, ruta fuera del directorio raíz
 * permitido (path traversal), etc.
 */
class StorageException extends HttpException
{
    public function __construct(string $message = 'Storage error', int $statusCode = 500)
    {
        parent::__construct($statusCode, $message);
    }
}
