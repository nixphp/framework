<?php

namespace PHPico\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends \LogicException implements NotFoundExceptionInterface
{
}
