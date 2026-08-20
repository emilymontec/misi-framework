<?php

declare(strict_types=1);

namespace Misi\Http;

/**
 * Respuesta JSON estándar de Misi.
 *
 * Formato de éxito:
 *   { "success": true, "data": {}, "message": "..." }
 *
 * Formato de error:
 *   { "success": false, "data": null, "message": "...", "errors": {} }
 */
final class JsonResponse extends Response
{
    public function __construct(array $payload, int $statusCode = 200)
    {
        parent::__construct(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            $statusCode
        );
        $this->header('Content-Type', 'application/json; charset=utf-8');
    }

    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): self
    {
        return new self([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }

    public static function error(string $message = 'Error', array $errors = [], int $statusCode = 400, mixed $data = null): self
    {
        return new self([
            'success' => false,
            'data' => $data,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
