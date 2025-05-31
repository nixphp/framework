<?php

use NixPHP\Core\App;
use NixPHP\Core\Container;
use NixPHP\Support\AppHolder;

$container = new Container();
$app = new App($container);

AppHolder::set($app);