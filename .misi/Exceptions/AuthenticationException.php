<?php

declare(strict_types=1);

namespace Misi\Exceptions;

/** El request no trae una sesión válida. Se traduce a un 401. */
class AuthenticationException extends HttpException
{
    public function __construct(string $message = 'No autenticado.')
    {
        parent::__construct(401, $message);
    }
}
