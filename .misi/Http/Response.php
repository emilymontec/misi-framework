<?php

declare(strict_types=1);

namespace Misi\Http;

/**
 * Respuesta HTTP básica de texto/HTML.
 * JsonResponse y RedirectResponse extienden esta clase.
 */
class Response
{
    /** @var array<string, string> */
    protected array $headers = [];

    public function __construct(
        protected string $content = '',
        protected int $statusCode = 200
    ) {
    }

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Usado por Application::applyDefaultSecurityHeaders() (Fase 15)
     * para no pisar una cabecera que el controlador ya fijó a propósito
     * (ej. un controlador que necesita permitir un iframe específico y
     * ya puso su propio X-Frame-Options).
     */
    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    public function status(int $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        echo $this->content;
    }
}
