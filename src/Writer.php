<?php

namespace Amp\Log;

interface Writer {
    /**
     * Writes the log message in a non-blocking way.
     *
     * @param string $level Log level.
     * @param string $message Log message with already replaced placeholders.
     * @param array  $context Context, in case structured logging is used.
     */
    public function log(string $level, string $message, array $context);
}
