<?php

namespace NixPHP\Exceptions;

class RouteNotFoundException extends HttpException
{
    public function __construct(string $message = 'Not found')
    {
        parent::__construct($message, 404);
    }
}