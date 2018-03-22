<?php

namespace Amp\Log\Writer;

use Amp\ByteStream\OutputStream;
use Amp\Log\Writer;

final class StreamWriter implements Writer {
    /** @var OutputStream */
    private $stream;

    /** @var callable */
    private $onResolve;

    /** @var \Throwable|null */
    private $exception;

    /**
     * @param OutputStream $outputStream
     */
    public function __construct(OutputStream $outputStream) {
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
            }

            throw $e;
        };
    }

    public function log(string $level, string $message, array $context) {
        if ($this->exception) {
            throw $this->exception;
        }

        $time = $context["time"] ?? \time();
        $date = @\date("Y-m-d H:i:s", $time);

        foreach (\explode("\n", $message) as $line) {
            // Strip any control characters...
            $line = \preg_replace('/[\x00-\x1F\x7F]/', '', $line);
            $this->stream->write($date . " {$level} {$line}\r\n")->onResolve($this->onResolve);
        }
    }
}
