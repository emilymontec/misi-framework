<?php

declare(strict_types=1);

namespace Misi\Http;

use Misi\Support\Session;

/**
 * Abstracción de la petición HTTP entrante.
 * Se construye una única vez a partir de las superglobales.
 */
final class Request
{
    /** @param array<string, mixed> $query
     *  @param array<string, mixed> $request
     *  @param array<string, mixed> $server
     *  @param array<string, mixed> $cookies
     *  @param array<string, mixed> $files
     */
    private function __construct(
        private readonly array $query,
        private readonly array $request,
        private readonly array $server,
        private readonly array $cookies,
        private readonly array $files,
        private readonly string $rawBody,
    ) {
    }

    public static function capture(): self
    {
        return new self(
            $_GET,
            $_POST,
            $_SERVER,
            $_COOKIE,
            $_FILES,
            file_get_contents('php://input') ?: ''
        );
    }

    public function method(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');

        // Permite simular PUT/PATCH/DELETE desde formularios HTML con _method
        if ($method === 'POST' && isset($this->request['_method'])) {
            $method = strtoupper((string) $this->request['_method']);
        }

        return $method;
    }

    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url((string) $uri, PHP_URL_PATH) ?: '/';

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type', '');
        return str_contains(strtolower($contentType), 'application/json');
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        if ($this->isJson()) {
            $decoded = json_decode($this->rawBody, true);
            return is_array($decoded) ? array_merge($this->query, $decoded) : $this->query;
        }

        return array_merge($this->query, $this->request);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalized = 'HTTP_' . str_replace('-', '_', strtoupper($key));
        return $this->server[$normalized] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Acceso a la sesión desde el Request, como pedía el diseño original
     * (Fase 2). Delega en Misi\Support\Session — Request en sí sigue
     * siendo un snapshot inmutable de las superglobales, no guarda
     * estado de sesión propio.
     */
    public function session(string $key, mixed $default = null): mixed
    {
        return Session::get($key, $default);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /**
     * Usado por Application (Fase 15) para decidir si envía
     * Strict-Transport-Security. Revisa dos señales:
     *
     *  1. HTTPS en $_SERVER: lo que pone Apache/Nginx cuando TLS
     *     termina en el propio servidor de la app.
     *  2. Cabecera X-Forwarded-Proto: lo que pone un proxy que termina
     *     TLS delante de la app (ej. Cloudflare, que es como
     *     InfinityFree entrega su SSL gratuito — ver DEPLOYMENT.md). Ahí Apache ve HTTP plano
     *     internamente aunque el visitante esté en HTTPS real.
     *
     * Confiar en X-Forwarded-Proto es seguro para este uso específico:
     * como mucho, un valor falsificado hace que se envíe una cabecera
     * HSTS de más sobre una conexión HTTP real, y los navegadores
     * ignoran HSTS recibido fuera de una conexión HTTPS genuina (no es
     * una decisión de autorización, así que no hay nada que un atacante
     * gane falsificándola).
     */
    public function isSecure(): bool
    {
        $https = strtolower((string) ($this->server['HTTPS'] ?? ''));
        if ($https !== '' && $https !== 'off') {
            return true;
        }

        return strtolower((string) $this->header('X-Forwarded-Proto', '')) === 'https';
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }
}
