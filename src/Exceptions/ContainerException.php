<?php
declare(strict_types=1);

namespace NixPHP\Exceptions;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
}
