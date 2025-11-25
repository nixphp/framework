<?php

namespace NixPHP\Enum;

enum Environment: string implements EnvironmentInterface
{

    case DEV = 'dev';
    case TEST = 'test';
    case PROD = 'prod';

}