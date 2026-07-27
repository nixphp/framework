<?php

declare(strict_types=1);

namespace NixPHP\Core;

use Psr\Http\Message\ResponseInterface;
use function NixPHP\event;

/**
 * Writes a PSR-7 response to the client.
 *
 * Two entry points exist on purpose: send() is the normal path and runs the
 * response events, emit() is the raw path used while an error is being
 * reported. Both terminate the request.
 */
final class ResponseEmitter
{

    /**
     * Emit a response and run the RESPONSE_HEADER, RESPONSE_BODY and
     * RESPONSE_END events
     *
     * @param ResponseInterface $response Response to send
     */
    public static function send(ResponseInterface $response): never
    {
        self::clearOutputBuffers();

        if (headers_sent()) {
            echo $response->getBody();
            exit(0);
        }

        $response = event()->dispatchForResponse(Event::RESPONSE_HEADER, $response) ?? $response;

        self::writeHead($response);

        event()->dispatch(Event::RESPONSE_BODY, $response);

        echo $response->getBody();

        event()->dispatch(Event::RESPONSE_END, $response);

        exit(0);
    }

    /**
     * Emit a response without touching the event system
     *
     * Used while handling a fatal error: the listeners are part of the
     * application that just failed, so running them again risks a second
     * exception on top of the one being reported.
     *
     * @param ResponseInterface $response Response to send
     */
    public static function emit(ResponseInterface $response): never
    {
        self::clearOutputBuffers();

        if (!headers_sent()) {
            self::writeHead($response);
        }

        echo $response->getBody();

        exit(0);
    }

    private static function writeHead(ResponseInterface $response): void
    {
        header(sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ));

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
    }

    private static function clearOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

}
