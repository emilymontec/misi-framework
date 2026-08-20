<?php

declare(strict_types=1);

namespace Misi\Support;

/**
 * Cargador minimalista de variables de entorno (.env).
 *
 * No se utiliza vlucas/dotenv para mantener cero dependencias externas
 * y garantizar que Misi funcione en hosting compartido sin `composer install`.
 */
final class Env
{
    private static bool $loaded = false;

    /** @var array<string, string> */
    private static array $variables = [];

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        if (is_file($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim(trim($value), "\"'");

                self::$variables[$name] = $value;

                if (getenv($name) === false) {
                    putenv("{$name}={$value}");
                }

                $_ENV[$name] = $value;
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$variables[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            '' => $default,
            default => $value,
        };
    }
}
