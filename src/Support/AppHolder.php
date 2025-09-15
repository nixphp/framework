<?php
declare(strict_types=1);

namespace NixPHP\Support;

use NixPHP\Core\App;
use NixPHP\Core\Container;

class AppHolder
{
    private static ?App $instance = null;

    public static function get(): App
    {
        if (self::$instance === null) {
            self::$instance = new App(new Container());
        }
        return self::$instance;
    }

    public static function set(App $app): void
    {
        self::$instance = $app;
    }
}