<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Logging;

use Throwable;

/**
 * Abstract class defining core logging functionality and expected behavior
 */
abstract class ClientBase
{
    /**
     * Logs a message classified as an error
     *
     * @param string    $message The message we intend to log
     * @param Throwable $event   Throwable event associated with the error
     * @param mixed[]   $data    Data associated with the error if any is present
     *
     * @return void
     */
    abstract public function error(string $message, $event = null, $data = null);

    /**
     * Logs a message classified as a debugging line
     *
     * @param string  $message The message we intend to log
     * @param mixed[] $data    Data associated with the error if any is present
     *
     * @return void
     */
    abstract public function debug(string $message, $data = null);

    /**
     * Logs a message classified as a warning
     *
     * @param string  $message The message we intend to log
     * @param mixed[] $data    Data associated with the error if any is present
     *
     * @return void
     */
    abstract public function warn(string $message, $data = null);

    /**
     * Logs a message classified as information for developers/administrators
     *
     * @param string  $message The message we intend to log
     * @param mixed[] $data    Data associated with the error if any is present
     *
     * @return void
     */
    abstract public function info(string $message, $data = null);
}
