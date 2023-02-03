<?php declare(strict_types=1);

namespace Amp\Log;

use Amp\ByteStream\WritableStream;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Psr\Log\LogLevel;

final class StreamHandler extends AbstractProcessingHandler
{
    private WritableStream $sink;

    /** @psalm-suppress ArgumentTypeCoercion */
    public function __construct(WritableStream $sink, string $level = LogLevel::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->sink = $sink;
    }

    protected function write(LogRecord $record): void
    {
        if (!\is_string($record->formatted)) {
            throw new \ValueError(\sprintf(
                '%s only supports writing string formatted log records, got "%s"',
                self::class,
                \get_debug_type($record->formatted),
            ));
        }

        $this->sink->write($record->formatted);
    }
}
