<?php

namespace Amp\Log\Writer;

use Amp\ByteStream\OutputStream;
use Amp\ByteStream\ResourceOutputStream;
use Amp\Log\Writer;
use Psr\Log\LogLevel;
use function Amp\Log\hasColorSupport;

final class ConsoleWriter implements Writer {
    /** @var OutputStream */
    private $writer;

    /** @var bool */
    private $colors;

    public function __construct() {
        $this->writer = new StreamWriter(new ResourceOutputStream(\STDOUT));
        $this->setAnsiColorOption();
    }

    public function log(int $time, string $level, string $message) {
        $level = $this->ansify($level);

        // Strip any control characters...
        $message = \preg_replace('/[\x00-\x1F\x7F]/', '', $message);

        $this->writer->log($time, $level, $message);
    }

    public function setAnsiColorOption(string $value = null) {
        $value = $value ?? (\getenv("AMP_LOG_COLOR") ?: (\defined("AMP_LOG_COLOR") ? \AMP_LOG_COLOR : false));
        if ($value === false || $value === '') {
            $value = "auto";
        }

        $value = \strtolower($value);
        switch ($value) {
            case "1":
            case "true":
            case "on":
                $this->colors = true;
                break;
            case "0":
            case "false":
            case "off":
                $this->colors = false;
                break;
            default:
                $this->colors = hasColorSupport();
                break;
        }
    }

    private function ansify(string $level): string {
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
                return "\033[1;31m{$level}\033[0m"; // bold + red
            case LogLevel::WARNING:
                return "\033[1;33m{$level}\033[0m"; // bold + yellow
            case LogLevel::NOTICE:
                return "\033[1;32m{$level}\033[0m"; // bold + green
            case LogLevel::INFO:
                return "\033[1;35m{$level}\033[0m"; // bold + magenta
            case LogLevel::DEBUG:
                return "\033[1;36m{$level}\033[0m"; // bold + cyan
            default:
                return "\033[1m{$level}\033[0m"; // bold
        }
    }
}
