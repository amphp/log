<?php

namespace Amp\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

final class Logger extends AbstractLogger {
    const LEVELS = [
        LogLevel::DEBUG => 8,
        LogLevel::INFO => 7,
        LogLevel::NOTICE => 6,
        LogLevel::WARNING => 5,
        LogLevel::ERROR => 4,
        LogLevel::CRITICAL => 3,
        LogLevel::ALERT => 2,
        LogLevel::EMERGENCY => 1,
    ];

    /** @var int */
    private $outputLevel;

    /** @var Writer */
    private $writer;

    /**
     * @param Writer $writer
     * @param string $logLevel Use the PSR-3 LogLevel constants.
     *
     * @throws \Error If the log-level is invalid.
     */
    public function __construct(Writer $writer, string $logLevel = LogLevel::INFO) {
        if (!isset(self::LEVELS[$logLevel])) {
            throw new \Error("Invalid log level: {$logLevel}");
        }

        $this->outputLevel = self::LEVELS[$logLevel];
        $this->writer = $writer;
    }

    public function log($level, $message, array $context = []) {
        if (!$this->shouldEmit($level)) {
            return;
        }

        $message = $this->formatMessage($message, $context);
        $this->writer->log($level, $message, $context);
    }

    private function shouldEmit(string $logLevel): bool {
        return isset(self::LEVELS[$logLevel])
            ? ($this->outputLevel >= self::LEVELS[$logLevel])
            : false;
    }

    private function formatMessage(string $message, array $context = []): string {
        foreach ($context as $key => $replacement) {
            // avoid invalid casts to string
            if (!\is_array($replacement) && (!\is_object($replacement) || \method_exists($replacement, '__toString'))) {
                $replacements["{{$key}}"] = (string) $replacement;
            }
        }

        if (isset($replacements)) {
            $message = \strtr($message, $replacements);
        }

        return $message;
    }
}
