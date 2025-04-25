<?php

namespace Tests\Unit;

use PHPico\Support\Session;
use Tests\PHPicoTestCase;

class SessionTest extends PhpicoTestCase
{

    public function testSessionInternals()
    {
        $session = new Session();
        $session->start(function() {return null;});
        $session->set('foo', 'bar');
        $this->assertSame('bar', $session->get('foo'));
        $session->forget('foo');
        $this->assertNull($session->get('foo'));
    }

    public function testSessionFlashMessage()
    {
        $session = new Session();
        $session->start(function() {return null;});

        $session->flash('foo', 'bar');
        $this->assertSame('bar', $session->getFlash('foo'));
        $this->assertNull($session->getFlash('foo'));

    }

}