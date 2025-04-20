<?php

namespace PHPico\Core;

class ErrorHandler
{

    public static function handleException(\Throwable $e)
    {
        http_response_code(500);

        $message = htmlspecialchars($e->getMessage());
        $file = htmlspecialchars($e->getFile());
        $line = (int)$e->getLine();
        $trace = htmlspecialchars($e->getTraceAsString());

        echo <<<HTML
            <!DOCTYPE html>
            <html lang="de">
            <head>
                <meta charset="UTF-8">
                <title>Fehler</title>
                <style>
                    body { 
                        font-family: sans-serif; 
                        background: #121212; 
                        color: #e0e0e0; 
                        padding: 2rem; 
                        margin: 0;
                    }
                    .error-box { 
                        background: #1e1e1e; 
                        border-radius: 8px; 
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); 
                        padding: 1.5rem; 
                        max-width: 800px; 
                        margin: 2rem auto; 
                    }
                    h1, h2 { 
                        color: #ff6f61; 
                    }
                    pre { 
                        background: #2b2b2b; 
                        padding: 1rem; 
                        border-radius: 4px; 
                        overflow-x: auto; 
                        font-size: 0.9rem;
                    }
                    p {
                        margin: 0.5rem 0;
                    }
                </style>
            </head>
            <body>
                <div class="error-box">
                    <h1>Ein Fehler ist aufgetreten</h1>
                    <p><strong>Nachricht:</strong> {$message}</p>
                    <p><strong>Datei:</strong> {$file}</p>
                    <p><strong>Zeile:</strong> {$line}</p>
                    <h2>Stacktrace:</h2>
                    <pre>{$trace}</pre>
                </div>
            </body>
            </html>
            HTML;
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