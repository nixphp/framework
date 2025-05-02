<?php

namespace NixPHP\Core;

use PHPUnit\Framework\Attributes\CoversNothing;
use function NixPHP\send_response;
use function NixPHP\view;
use function NixPHP\response;

class ErrorHandler
{

    #[CoversNothing] public static function handleException(\Throwable $e): void
    {
        $message = htmlspecialchars($e->getMessage());
        $file = htmlspecialchars($e->getFile());
        $line = (int)$e->getLine();
        $trace = htmlspecialchars($e->getTraceAsString());
        send_response(
            response(
                view('errors.500' . $e->getCode(), compact('message', 'file', 'line', 'trace')),
                500
            )
        );
    }

    /**
     * @throws \ErrorException
     */
    #[CoversNothing] public static function handleError($errno, $errstr, $errfile, $errline): \ErrorException
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

}