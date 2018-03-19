<?php

namespace Amp\Log\Writer;

use Amp\ByteStream\OutputStream;
use Amp\Log\Writer;

final class StreamWriter implements Writer {
    /** @var OutputStream */
    private $stream;

    /** @var callable */
    private $onResolve;

    /**
     * @param OutputStream $outputStream
     * @param callable|null $onException Invoked if writing to the stream fails. If no callback is given, the exception
     *     is forwarded to the loop exception handler. If $onException throws, that exception is also forwared to the
     *     loop exception handler.
     */
    public function __construct(OutputStream $outputStream, callable $onException = null) {
        $this->stream = $outputStream;

        $stream = &$this->stream;
        $this->onResolve = static function (\Throwable $exception = null) use (&$stream, $onException) {
            if (!$stream) {
                return; // Prior write already failed, ignore this failure.
            }

            if ($exception) {
                $stream = null;

                if (!$onException) {
                    throw $exception; // Forwards exception to loop error handler.
                }

                $onException($exception);
            }
        };
    }

    public function log(string $level, string $message, array $context) {
        if (!$this->stream) {
            return;
        }

        $time = $context["time"] ?? \time();
        $this->stream->write(@\date("Y-m-d H:i:s", $time) . " {$level} {$message}\r\n")->onResolve($this->onResolve);
    }
}
