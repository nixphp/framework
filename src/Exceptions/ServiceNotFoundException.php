<?php
declare(strict_types=1);

namespace NixPHP\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends \LogicException implements NotFoundExceptionInterface
{
}
