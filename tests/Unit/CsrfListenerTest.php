<?php

namespace Tests\Unit;

use Nyholm\Psr7\ServerRequest;
use PHPico\Core\Events\CsrfListener;
use PHPico\Exceptions\AbortException;
use Tests\PHPicoTestCase;
use function PHPico\session;

class CsrfListenerTest extends PHPicoTestCase
{

    public function testSuccessful()
    {
        $this->expectNotToPerformAssertions();

        session()->start();
        $_SESSION['_csrf'] = 'test';
        $requestMock = new ServerRequest('POST', '/test');
        $requestMock = $requestMock->withParsedBody(['_csrf' => 'test']);
        $listener = new CsrfListener();
        $listener->handle($requestMock);
    }

    public function testShouldNotInterceptWithDifferentMethod()
    {
        $this->expectNotToPerformAssertions();

        $request = new ServerRequest('GET', '/test');
        $listener = new CsrfListener();
        $listener->handle($request);
    }

    public function testMissingCsrfToken()
    {
        $this->expectException(AbortException::class);

        $requestMock = new ServerRequest('POST', '/test');
        $listener = new CsrfListener();
        $listener->handle($requestMock);
    }

    public function testWrongCsrfToken()
    {
        $this->expectException(AbortException::class);

        session()->start();
        $_SESSION['_csrf'] = 'other';
        $requestMock = new ServerRequest('POST', '/test');
        $requestMock = $requestMock->withParsedBody(['_csrf' => 'test']);
        $listener = new CsrfListener();
        $listener->handle($requestMock);
    }

    public function testShouldIgnoreWhenAuthorizationHeaderIsPresent()
    {
        $this->expectNotToPerformAssertions();

        $requestMock = new ServerRequest('POST', '/test');
        $requestMock = $requestMock->withHeader('Authorization', 'Bearer test');
        $listener = new CsrfListener();
        $listener->handle($requestMock);
    }

}