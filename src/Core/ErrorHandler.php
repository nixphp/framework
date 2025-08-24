<?php

namespace NixPHP\Core;

use function NixPHP\send_response;
use function NixPHP\simple_view;
use function NixPHP\response;

class ErrorHandler
{

    public static function handleException(\Throwable $e): void
    {
        $message = htmlspecialchars($e->getMessage());
        $file = htmlspecialchars($e->getFile());
        $line = (int)$e->getLine();
        $trace = htmlspecialchars($e->getTraceAsString());
        send_response(
            response(
                simple_view('errors/default' . $e->getCode(), compact('message', 'file', 'line', 'trace')),
                500
            )
        );
    }

    /**
     * @throws \ErrorException
     */
    public static function handleError($errno, $errstr, $errfile, $errline): \ErrorException
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

}