<?php

namespace NixPHP\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Log implements LoggerInterface
{

    private string $logFile;

    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
    }

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $date = date('Y-m-d H:i:s');
        $interpolatedMessage = $this->interpolate($message, $context);
        $logLine = "[$date] [$level] $interpolatedMessage" . PHP_EOL;
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    private function interpolate(string $message, array $context): string
    {
        // Placeholder {key} will be replaced by variables in $contexts
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = (string) $val;
        }

        return strtr($message, $replace);
    }



}