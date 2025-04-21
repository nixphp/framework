<?php

namespace PHPico\Exceptions;

class RouteNotFoundException extends HttpException
{
    public function __construct(string $message = 'Route not found')
    {
        parent::__construct($message, 404);
    }
}