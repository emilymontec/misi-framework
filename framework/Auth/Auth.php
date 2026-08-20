<?php

declare(strict_types=1);

namespace Misi\Auth;

use Misi\Support\Session;

/**
 * Autenticación basada en sesión PHP nativa.
 *
 * API estática (Auth::attempt(), Auth::user()...) consistente con
 * Session, para poder usarse desde cualquier controlador sin
 * inyecciones ni contenedor de DI (ver Router, Fase 3).
 *
 * Asume una tabla `users` con al menos: id, email, password (hash de
 * password_hash()). Cada proyecto puede tener columnas adicionales; Auth
 * solo lee lo que necesita.
 *
 * No implementa: JWT, OAuth, "remember me" persistente, ni multi-guard
 * (varios tipos de usuario autenticándose por separado). Se evalúan solo
 * si un proyecto real los necesita — por ejemplo, una app móvil que
 * consuma la misma API sin cookies de sesión.
 */
final class Auth
{
    private const SESSION_KEY = '_auth_user_id';

    /** @var array<string, mixed>|null|false false = aún no resuelto en esta request */
    private static array|null|false $resolvedUser = false;

    /**
     * Intenta autenticar con email + password. Si es correcto, inicia
     * sesión (con regeneración de ID) y retorna true.
     */
    public static function attempt(string $email, string $password): bool
    {
        $user = app()->database()->selectOne(
            'SELECT * FROM users WHERE email = ?',
            [$email]
        );

        if ($user === null || !password_verify($password, (string) $user['password'])) {
            return false;
        }

        self::login((int) $user['id']);

        return true;
    }

    /** Inicia sesión para un ID de usuario ya verificado (ej. tras un registro). */
    public static function login(int $userId): void
    {
        // Regenerar el ID de sesión ANTES de guardar el usuario mitiga
        // session fixation: un ID de sesión robado antes del login deja
        // de ser válido.
        Session::regenerate();
        Session::put(self::SESSION_KEY, $userId);
        self::$resolvedUser = false;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$resolvedUser = false;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);
        return $id !== null ? (int) $id : null;
    }

    /**
     * Usuario autenticado actual (sin el hash de password), o null si es
     * invitado. Se resuelve una sola vez por request y se cachea.
     *
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        if (self::$resolvedUser !== false) {
            return self::$resolvedUser;
        }

        $id = self::id();

        if ($id === null) {
            self::$resolvedUser = null;
            return null;
        }

        $user = app()->database()->selectOne('SELECT * FROM users WHERE id = ?', [$id]);

        if ($user !== null) {
            unset($user['password']);
        }

        self::$resolvedUser = $user;

        return $user;
    }

    /**
     * Verifica si el usuario autenticado tiene un permiso, vía las tablas
     * roles/permissions/role_user/permission_role (Fase 6.1). Convención
     * de nombre de permiso: "recurso.accion" (ej. "orders.create").
     *
     * Devuelve false para invitados o si el permiso no existe — nunca
     * lanza excepción por falta de autorización (eso lo decide el
     * controlador o middleware que llama a can()).
     */
    public static function can(string $permission): bool
    {
        $id = self::id();

        if ($id === null) {
            return false;
        }

        $row = app()->database()->selectOne(
            'SELECT 1
             FROM permissions p
             INNER JOIN permission_role pr ON pr.permission_id = p.id
             INNER JOIN role_user ru ON ru.role_id = pr.role_id
             WHERE ru.user_id = ? AND p.name = ?
             LIMIT 1',
            [$id, $permission]
        );

        return $row !== null;
    }
}
