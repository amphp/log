<?php

namespace Amp\Log;

interface Writer {
    /**
     * Writes the log message in a non-block way.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log(string $level, string $message, array $context);
}
