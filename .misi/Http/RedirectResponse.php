<?php

declare(strict_types=1);

namespace Misi\Http;

final class RedirectResponse extends Response
{
    public function __construct(string $url, int $statusCode = 302)
    {
        parent::__construct('', $statusCode);
        $this->header('Location', $url);
    }
}
