<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

use NixPHP\Support\Stopwatch;

class NixPHPTestCase extends TestCase
{
    protected function tearDown(): void
    {
        try {
            Stopwatch::stop('app');
        } catch (\RuntimeException) {
            // ignore when stopwatch was not running
        }

        parent::tearDown();
    }

}