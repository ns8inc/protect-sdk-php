<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Logging;

/**
 * HTTP/Rest client that allows the NS8 SDK to communicate with NS8 services
 */
abstract class ClientBase
{
    /**
     * @param string    $message   The message we intend to log
     * @param Exception $exception Exception object associated with the error.
     * @param mixed[]   $data      Data associated with the error if any is present
     *
     * @return void
     */
    abstract public function error(string $message, ?Exception $exception = null, ?array $data = null) : void;

    /**
     * Logs a message classified as a debugging line
     *
     * @param string  $message The message we intend to log
     * @param mixed[] $data    Data associated with the error if any is present
     *
     * @return void
     */
    abstract public function debug(string $message, ?array $data = null) : void;

    /**
     * Logs a message classified as a warning
     *
     * @param string  $message The message we intend to log
     * @param mixed[] $data    Data associated with the error if any is present
     *
     * @return void
     */
    abstract public function warn(string $message, ?array $data = null) : void;

    /**
     * Logs a message classified as information for developers/administrators
     *
     * @param string  $message The message we intend to log
     * @param mixed[] $data    Data associated with the error if any is present
     *
     * @return void
     */
    abstract public function info(string $message, ?array $data = null) : void;
}
