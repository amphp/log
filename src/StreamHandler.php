<?php

namespace Amp\Log;

use Amp\ByteStream\WritableStream;
use Monolog\Handler\AbstractProcessingHandler;
use Psr\Log\LogLevel;

final class StreamHandler extends AbstractProcessingHandler
{
    private WritableStream $sink;

    public function __construct(WritableStream $sink, string $level = LogLevel::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->sink = $sink;
    }

    protected function write(array $record): void
    {
        $this->sink->write((string) $record['formatted']);
    }
}
