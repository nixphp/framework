<?php
declare(strict_types=1);

namespace NixPHP\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Log implements LoggerInterface
{

    private string $logFile;

    /**
     * Create a new Log instance
     *
     * @param string $logFile Path to the log file
     */
    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
    }

    /**
     * System is unusable.
     *
     * @param mixed                $message
     * @param array<string, mixed> $context
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * @param mixed                $message
     * @param array<string, mixed> $context
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * @param mixed                $message
     * @param array<string, mixed> $context
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
     *
     * @param mixed                $message
     * @param array<string, mixed> $context
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * @param mixed                $message
     * @param array<string, mixed> $context
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param mixed                $message
     * @param array<string, mixed> $context
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * @param mixed                $message
     * @param array<string, mixed> $context
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param mixed                $message
     * @param array<string, mixed> $context
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed                $level
     * @param mixed                $message
     * @param array<string, mixed> $context
     */
    public function log($level, mixed $message, array $context = []): void
    {
        $date = date('Y-m-d H:i:s');
        $interpolatedMessage = $this->interpolate($message, $context);
        $logLine = "[$date] [$level] $interpolatedMessage" . PHP_EOL;
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string              $message
     * @param array<string,mixed> $context
     *
     * @return string
     */
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