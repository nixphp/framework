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

}