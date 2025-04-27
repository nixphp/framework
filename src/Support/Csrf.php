<?php

namespace PHPico\Support;

use function PHPico\session;

class Csrf
{

    public function generate(): string
    {
        session()->start();
        $csrfToken = bin2hex(random_bytes(16));
        session()->set('_csrf', $csrfToken);
        return $csrfToken;
    }

    public function validate(string $token): bool
    {
        session()->start();
        $csrfToken = session()->get('_csrf');
        return $csrfToken === $token;
    }

}