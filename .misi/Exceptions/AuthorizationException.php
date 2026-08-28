<?php

declare(strict_types=1);

namespace Misi\Exceptions;

/** Hay sesión, pero no el permiso/estado necesario. Se traduce a un 403. */
class AuthorizationException extends HttpException
{
    public function __construct(string $message = 'No autorizado.')
    {
        parent::__construct(403, $message);
    }
}
