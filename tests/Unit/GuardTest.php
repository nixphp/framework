<?php

namespace Tests\Unit;

use NixPHP\Support\Guard;
use Tests\NixPHPTestCase;

class GuardTest extends NixPHPTestCase
{

    public function testSafeOutputWithRegularString()
    {
        $guard = new Guard();
        $output = $guard->safeOutput('test');
        $this->assertSame('test', $output);
    }

    public function testSafeOutputWithRegularArray()
    {
        $guard = new Guard();
        $output = $guard->safeOutput(['test']);
        $this->assertSame(['test'], $output);
    }

    public function testSafeOutputWithMaliciousString()
    {
        $guard = new Guard();
        $output = $guard->safeOutput('<script>Test</script>');
        $this->assertSame('&lt;script&gt;Test&lt;/script&gt;', $output);
    }

    public function testSafeOutputWithMaliciousArray()
    {
        $guard = new Guard();
        $output = $guard->safeOutput(['<script>Test</script>']);
        $this->assertSame(['&lt;script&gt;Test&lt;/script&gt;'], $output);
    }

    public function testSafePathSuccess()
    {
        $guard = new Guard();
        $output = $guard->safePath('views/valid/file.php');
        $this->assertSame('views/valid/file.php', $output);
    }

    public function testSafePathWithRoot()
    {
        $this->expectException(\InvalidArgumentException::class);

        $guard = new Guard();
        $output = $guard->safePath('/views/valid/file.php');
    }

    public function testSafePathWithTraversingPath()
    {
        $this->expectException(\InvalidArgumentException::class);

        $maliciousPath = '/../../../home/user/.ssh/id_rsa';
        $guard = new Guard();
        $guard->safePath($maliciousPath);
    }

    public function testSafePathWithInvalidChars()
    {
        $this->expectException(\InvalidArgumentException::class);

        $maliciousPath = '/<script></script>';
        $guard = new Guard();
        $guard->safePath($maliciousPath);
    }

    public function testHelperFunction()
    {
        $this->assertInstanceOf(Guard::class, \NixPHP\guard() );;
    }

}