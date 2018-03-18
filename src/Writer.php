<?php

namespace Amp\Log;

interface Writer {
    /**
     * Writes the log message in a non-block way.
     *
     * @param int $time
     * @param string $level
     * @param string $message
     */
    public function log(int $time, string $level, string $message);
}
