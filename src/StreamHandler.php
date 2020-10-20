<?php

namespace Amp\Log;

use Amp\ByteStream\OutputStream;
use Monolog\Handler\AbstractProcessingHandler;
use Psr\Log\LogLevel;
use function Amp\async;

final class StreamHandler extends AbstractProcessingHandler
{
    private OutputStream $stream;

    /** @var callable */
    private $onResolve;

    private ?\Throwable $exception = null;

    /**
     * @param OutputStream $outputStream
     * @param string       $level
     * @param bool         $bubble
     */
    public function __construct(OutputStream $outputStream, string $level = LogLevel::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->stream = $outputStream;

        $stream = &$this->stream;
        $exception = &$this->exception;
        $this->onResolve = static function (\Throwable $e = null) use (&$stream, &$exception) {
            if (!$stream) {
                return; // Prior write already failed, ignore this failure.
            }

            if ($e) {
                $stream = null;
                $exception = $e;

                throw $e;
            }
        };
    }

    /**
     * Writes the record down to the log of the implementing handler.
     *
     * @param  array $record
     *
     * @return void
     */
    protected function write(array $record): void
    {
        if ($this->exception) {
            throw $this->exception;
        }

        async(fn() => $this->stream->write((string) $record['formatted']))->onResolve($this->onResolve);
    }
}
