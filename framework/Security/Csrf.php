<?php

declare(strict_types=1);

namespace Misi\Security;

use Misi\Http\Request;
use Misi\Support\Session;

/**
 * Protección CSRF basada en token de sesión.
 *
 * El token se genera una sola vez por sesión (no en cada request) y se
 * compara con hash_equals() para evitar timing attacks. Se acepta el
 * token tanto en el body (`_token`, para forms HTML tradicionales) como
 * en el header `X-CSRF-Token` (para fetch/AJAX) — ver docs/security.md.
 *
 * No se implementa un token distinto por formulario/petición (como hacen
 * algunos frameworks para mitigar BREACH): un token por sesión es
 * suficiente para el tamaño de proyecto que Misi apunta a resolver, y
 * mantiene la implementación simple de razonar.
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    /** Devuelve el token de la sesión actual, generándolo si no existe. */
    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);

        if (!is_string($token) || $token === '') {
            $token = self::regenerate();
        }

        return $token;
    }

    /** Genera un token nuevo y lo guarda en sesión, descartando el anterior. */
    public static function regenerate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::put(self::SESSION_KEY, $token);

        return $token;
    }

    /** Campo oculto listo para insertar en un `<form>` HTML tradicional. */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');

        return "<input type=\"hidden\" name=\"_token\" value=\"{$token}\">";
    }

    /**
     * Valida el token recibido en el request actual contra el de sesión.
     * Acepta `_token` en el body (form/JSON) o el header `X-CSRF-Token`.
     */
    public static function validate(Request $request): bool
    {
        $submitted = $request->input('_token') ?? $request->header('X-CSRF-Token');

        if (!is_string($submitted) || $submitted === '') {
            return false;
        }

        return hash_equals(self::token(), $submitted);
    }
}
