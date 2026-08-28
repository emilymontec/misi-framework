<?php

declare(strict_types=1);

namespace Misi\Logging;

/**
 * Logger simple a archivo, con niveles y rotación diaria.
 *
 * Un archivo por día (`misi-YYYY-MM-DD.log`) — suficiente para el
 * tamaño de proyecto que Misi apunta a resolver, sin necesitar
 * `logrotate` ni ninguna herramienta externa (funciona igual en
 * cualquier hosting compartido).
 *
 * No se implementa: handlers múltiples (syslog, Slack, servicios
 * externos tipo Sentry), formato estructurado (JSON por línea), ni
 * niveles adicionales fuera de PSR-3 básico. Se evalúan solo si un
 * proyecto real los necesita.
 */
final class Logger
{
    private const LEVELS = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4,
    ];

    /**
     * Fragmentos de nombre de clave que nunca se escriben en el log:
     * si una clave del contexto contiene alguno de estos (sin importar
     * mayúsculas/minúsculas), su valor se reemplaza por [REDACTED].
     */
    private const SENSITIVE_KEY_FRAGMENTS = [
        'password', 'contraseña', 'token', 'secret', 'api_key', 'apikey',
        'authorization', 'csrf', 'credit_card', 'cvv', 'tarjeta',
    ];

    private readonly int $minLevel;

    public function __construct(
        private readonly string $path,
        string $minLevel = 'debug'
    ) {
        $this->minLevel = self::LEVELS[$minLevel] ?? self::LEVELS['debug'];
    }

    /** @param array<string, mixed> $context */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /** @param array<string, mixed> $context */
    private function log(string $level, string $message, array $context): void
    {
        if ((self::LEVELS[$level] ?? 0) < $this->minLevel) {
            return;
        }

        $this->ensureDirectoryExists($this->path);

        $safeContext = $this->redact($context);
        $suffix = $safeContext !== [] ? ' ' . json_encode($safeContext, JSON_UNESCAPED_UNICODE) : '';

        $line = sprintf(
            '[%s] %s %s%s',
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $suffix
        );

        file_put_contents($this->filePath(), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function filePath(): string
    {
        return rtrim($this->path, '/') . '/misi-' . date('Y-m-d') . '.log';
    }

    /**
     * Enmascara recursivamente cualquier clave del contexto que
     * coincida con SENSITIVE_KEY_FRAGMENTS. No intenta adivinar sobre
     * el contenido del mensaje principal ($message) — esa parte es
     * responsabilidad de quien llama al Logger (nunca interpolar un
     * password directo en el mensaje).
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function redact(array $context): array
    {
        $redacted = [];

        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $redacted[$key] = $this->redact($value);
                continue;
            }

            $redacted[$key] = $this->isSensitiveKey((string) $key) ? '[REDACTED]' : $value;
        }

        return $redacted;
    }

    private function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);

        foreach (self::SENSITIVE_KEY_FRAGMENTS as $fragment) {
            if (str_contains($key, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException("No fue posible crear el directorio de logs: {$dir}");
        }
    }
}
