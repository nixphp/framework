<?php

namespace Tests\Unit;

use PHPico\Support\Guard;
use Tests\PHPicoTestCase;

class GuardTest extends PHPicoTestCase
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