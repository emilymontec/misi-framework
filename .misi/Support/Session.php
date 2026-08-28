<?php

declare(strict_types=1);

namespace Misi\Support;

/**
 * Wrapper estático sobre las sesiones nativas de PHP.
 *
 * API intencionalmente estática (Session::get(), Session::put()...) para
 * que Auth y los controladores puedan usarla sin pasarla por constructor,
 * igual que Env. La sesión se inicia de forma perezosa: si una ruta nunca
 * toca Session/Auth, nunca se manda cookie de sesión.
 */
final class Session
{
    private static bool $configured = false;
    private static bool $started = false;

    /**
     * Configura nombre de cookie y parámetros de seguridad ANTES de
     * iniciar la sesión. Debe llamarse una sola vez, desde Application.
     */
    public static function configure(
        string $name = 'misi_session',
        int $lifetime = 0,
        bool $secure = false,
        string $sameSite = 'Lax'
    ): void {
        if (self::$configured || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name($name);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'httponly' => true,
            'secure' => $secure,
            'samesite' => $sameSite,
        ]);

        self::$configured = true;
    }

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        session_start();
        self::$started = true;
        self::ageFlashData();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::start();
        return array_key_exists($key, $_SESSION);
    }

    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /** @return array<string, mixed> */
    public static function all(): array
    {
        self::start();
        return $_SESSION;
    }

    /** Vacía toda la sesión (no la destruye a nivel de cookie, ver destroy()). */
    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }

    /** Destruye por completo la sesión actual (usado en logout). */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        self::$started = false;
    }

    /**
     * Regenera el ID de sesión (mitiga session fixation). Se llama
     * automáticamente al hacer login/logout desde Auth.
     */
    public static function regenerate(bool $deleteOldSession = true): void
    {
        self::start();
        session_regenerate_id($deleteOldSession);
    }

    public static function id(): string
    {
        self::start();
        return session_id();
    }

    /**
     * Guarda un valor "flash": disponible en la request SIGUIENTE a esta,
     * y solo en esa (típico para mensajes de éxito/error tras un redirect).
     */
    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash']['new'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION['_flash']['old'][$key] ?? $default;
    }

    public static function hasFlash(string $key): bool
    {
        self::start();
        return array_key_exists($key, $_SESSION['_flash']['old'] ?? []);
    }

    /** Envejece los datos flash: lo "nuevo" de la request anterior pasa a "viejo" (disponible ahora). */
    private static function ageFlashData(): void
    {
        $_SESSION['_flash']['old'] = $_SESSION['_flash']['new'] ?? [];
        $_SESSION['_flash']['new'] = [];
    }
}
