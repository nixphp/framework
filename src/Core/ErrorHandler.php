<?php

namespace PHPico\Core;

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

        http_response_code(500);
        echo view('errors.500', compact('message', 'file', 'line', 'trace'));
    }

    /**
     * @throws \ErrorException
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        // Wandelt Fehler in eine ErrorException um → wird dann von handleException gefangen
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

}