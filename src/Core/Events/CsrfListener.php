<?php

namespace PHPico\Core\Events;

use PHPico\Exceptions\AbortException;
use Psr\Http\Message\ServerRequestInterface;
use function PHPico\abort;
use function PHPico\guard;

class CsrfListener
{

    /**
     * @param ServerRequestInterface $request
     *
     * @return void
     * @throws AbortException
     */
    public function handle(ServerRequestInterface $request): void
    {
        if (!\in_array($request->getMethod(), ['POST','PUT','DELETE'], true)) {
            return;
        }

        if ($request->hasHeader('Authorization')) {
            return;
        }

        $csrfToken = $request->getParsedBody()['_csrf'] ?? $request->getHeader('X-CSRF-Token') ?? null;

        if (empty($csrfToken)) {
            abort(400, 'CSRF token missing.');
        }

        if (false === guard()->csrf()->validate($csrfToken)) {
            abort(400, 'CSRF token invalid.');
        }
    }
}