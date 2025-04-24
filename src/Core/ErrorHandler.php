<?php

namespace PHPico\Core;

use function PHPico\send_response;
use function PHPico\view;
use function PHPico\response;

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
                view('errors.500', compact('message', 'file', 'line', 'trace')),
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