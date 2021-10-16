<?php

namespace Amp\Log;

use Amp\ByteStream\OutputStream;
use Monolog\Handler\AbstractProcessingHandler;
use Psr\Log\LogLevel;

final class StreamHandler extends AbstractProcessingHandler
{
    private OutputStream $stream;

    /**
     * @param OutputStream $outputStream
     * @param string       $level
     * @param bool         $bubble
     */
    public function __construct(OutputStream $outputStream, string $level = LogLevel::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->stream = $outputStream;
    }

    /**
     * Writes the record down to the log of the implementing handler.
     *
     * @param array $record
     *
     * @return void
     */
    protected function write(array $record): void
    {
        $this->stream->write((string) $record['formatted'])->await();
    }
}
