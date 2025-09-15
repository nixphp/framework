<?php
declare(strict_types=1);

namespace NixPHP\Core;

use function NixPHP\send_response;
use function NixPHP\simple_view;
use function NixPHP\response;

class ErrorHandler
{

    /**
     * Handles uncaught exceptions by rendering an error view
     *
     * Sanitizes exception details and renders them using the error view template,
     * then sends the response with HTTP 500 status code
     *
     * @param \Throwable $e The uncaught exception to handle
     *
     * @return void
     */
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
     * Converts PHP errors to ErrorException instances
     *
     * @param int    $errno   The error reporting level
     * @param string $errstr  The error message
     * @param string $errfile The file where the error occurred
     * @param int    $errline The line number where the error occurred
     *
     * @return \ErrorException The converted error exception
     * @throws \ErrorException Always throws the error as an exception
     */
    public static function handleError($errno, $errstr, $errfile, $errline): \ErrorException
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

}