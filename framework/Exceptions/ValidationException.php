<?php

declare(strict_types=1);

namespace Misi\Exceptions;

/**
 * Excepción lanzada por el Validator (Fase 5).
 * Se define ahora para que el Router/Application ya sepan manejarla.
 */
class ValidationException extends HttpException
{
    /** @param array<string, array<int, string>> $errors */
    public function __construct(private readonly array $errors, string $message = 'Validation failed')
    {
        parent::__construct(422, $message);
    }

    /** @return array<string, array<int, string>> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
