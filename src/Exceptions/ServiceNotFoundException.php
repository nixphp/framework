<?php

namespace NixPHP\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends \LogicException implements NotFoundExceptionInterface
{
}
