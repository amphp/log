<?php

namespace Amp\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\LogRecord;

final class ConsoleFormatter extends LineFormatter
{
    public const DEFAULT_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\r\n";

    private readonly bool $colors;

    public function __construct(
        ?string $format = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false,
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

    protected function normalizeRecord(LogRecord $record): array
    {
        $fields = parent::normalizeRecord($record);

        if ($this->colors) {
            $fields['level_name'] = $this->ansifyLevel($record->level);
            $fields['channel'] = "\033[1m{$record->channel}\033[0m";
        }

        return $fields;
    }

    private function determineAnsiColorOption(): bool
    {
        $value = \getenv("AMP_LOG_COLOR");
        if ($value === false || $value === '') {
            $value = "auto";
        }

        return match (\strtolower($value)) {
            "1", "true", "on" => true,
            "0", "false", "off" => false,
            default => hasColorSupport(),
        };
    }

    private function ansifyLevel(Level $level): string
    {
        return "\033[" . match ($level) {
            Level::Emergency => "43",
            Level::Alert => "45",
            Level::Critical => "41",
            Level::Error => "0;31",
            Level::Warning => "0;33",
            Level::Notice => "0;32",
            Level::Info => "0;34",
            Level::Debug => "0;36",
        } . "m" . $level->toPsrLogLevel() . "\033[0m";
    }
}
