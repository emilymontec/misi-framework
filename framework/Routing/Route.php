<?php

declare(strict_types=1);

namespace Misi\Routing;

/**
 * Representa una ruta individual registrada en el Router.
 */
final class Route
{
    private string $pattern;

    /** @var array<int, string> */
    private array $paramNames = [];

    /**
     * @param string $method
     * @param string $uri
     * @param callable|array{0: class-string, 1: string} $handler
     * @param array<int, string> $middleware
     */
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly mixed $handler,
        public readonly array $middleware = []
    ) {
        $this->compile();
    }

    private function compile(): void
    {
        // {param} -> segmento simple (sin "/"). {param*} -> catch-all,
        // incluye "/" — necesario para servir rutas de Storage con
        // subdirectorios (ej. /storage/avatars/archivo.jpg). Se agrega
        // ahora porque Storage (Fase 8) lo necesita de verdad, no como
        // abstracción especulativa (ver docs/routing.md).
        $pattern = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)(\*)?\}#',
            function (array $matches) {
                $this->paramNames[] = $matches[1];
                return isset($matches[2]) ? '(.+)' : '([^/]+)';
            },
            $this->uri
        ) ?? $this->uri;

        $this->pattern = '#^' . $pattern . '$#';
    }

    /**
     * Intenta hacer match contra la URI dada.
     * Devuelve los parámetros extraídos (asociativos) o null si no coincide.
     *
     * @return array<string, string>|null
     */
    public function match(string $uri): ?array
    {
        if (!preg_match($this->pattern, $uri, $matches)) {
            return null;
        }

        array_shift($matches);

        return array_combine($this->paramNames, $matches) ?: [];
    }
}
