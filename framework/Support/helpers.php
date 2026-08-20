<?php

declare(strict_types=1);

use Misi\Core\Application;
use Misi\Http\Request;
use Misi\Security\Csrf;

/**
 * Helpers globales de Misi.
 *
 * Deliberadamente pocos: app() (Fase 5) y los tres de CSRF (Fase 7), que
 * el diseño original pedía explícitamente como funciones sueltas
 * (csrf_token(), csrf_field(), csrf_validate()), no como métodos
 * estáticos de una clase que haya que importar en cada vista/controlador.
 * El resto de helpers listados en el roadmap original (asset(), url(),
 * redirect(), old(), config()) se agregan en su fase correspondiente
 * (Fase 13, UI utilities) — no todos de una vez, para no convertir este
 * archivo en un "helpers gigante" desde el día uno.
 */

if (!function_exists('app')) {
    function app(): Application
    {
        return Application::current();
    }
}

if (!function_exists('csrf_token')) {
    /** Token CSRF de la sesión actual (lo genera si no existe todavía). */
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    /** `<input type="hidden" name="_token" ...>` listo para un `<form>`. */
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_validate')) {
    /**
     * Valida el token CSRF del request actual. Fuera de un controlador
     * (donde ya se tiene el objeto Request a mano), esto reconstruye un
     * Request::capture() fresco — barato, ya que solo lee superglobales.
     */
    function csrf_validate(): bool
    {
        return Csrf::validate(Request::capture());
    }
}
