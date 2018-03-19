<?php

namespace Amp\Log\Writer;

use Amp\ByteStream\OutputStream;
use Amp\Log\Writer;
use Amp\Promise;

final class StreamWriter implements Writer {
    /** @var OutputStream */
    private $stream;

    public function __construct(OutputStream $outputStream) {
        $this->stream = $outputStream;
    }

    public function log(string $level, string $message, array $context) {
        $time = $context["time"] ?? \time();
        Promise\rethrow($this->stream->write(@\date("Y-m-d H:i:s", $time) . " {$level} {$message}\r\n"));
    }
}
