<?php

namespace Amp\Log;

use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;

final class ConsoleFormatter extends LineFormatter
{
    public const DEFAULT_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\r\n";

    private bool $colors;

    public function __construct(
        ?string $format = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false
    ) {
        parent::__construct(
            $format ?? self::DEFAULT_FORMAT,
            $dateFormat,
            $allowInlineLineBreaks,
            $ignoreEmptyContextAndExtra
        );

        $this->includeStacktraces = false;
        $this->colors = $this->determineAnsiColorOption();
    }

    public function format(array $record): string
    {
        if ($this->colors) {
            $record['level_name'] = $this->ansifyLevel($record['level_name']);
            $record['channel'] = "\033[1m{$record['channel']}\033[0m";
        }

        return parent::format($record);
    }

    private function determineAnsiColorOption(): bool
    {
        $value = \getenv("AMP_LOG_COLOR");
        if ($value === false || $value === '') {
            $value = "auto";
        }

        $value = \strtolower($value);
        return match ($value) {
            "1", "true", "on" => true,
            "0", "false", "off" => false,
            default => hasColorSupport(),
        };
    }

    private function ansifyLevel(string $level): string
    {
        $level = \strtolower($level);

        return match ($level) {
            LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR => "\033[1;31m{$level}\033[0m",
            LogLevel::WARNING => "\033[1;33m{$level}\033[0m",
            LogLevel::NOTICE => "\033[1;32m{$level}\033[0m",
            LogLevel::INFO => "\033[1;35m{$level}\033[0m",
            LogLevel::DEBUG => "\033[1;36m{$level}\033[0m",
            default => "\033[1m{$level}\033[0m",
        };
    }
}
