<?php
declare(strict_types=1);

namespace NixPHP\Exceptions;

class RouteNotFoundException extends HttpException
{
    public function __construct(string $message = 'Not found')
    {
        parent::__construct($message, 404);
    }
}