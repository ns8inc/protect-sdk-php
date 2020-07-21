<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Queue;

use Laminas\Http\Client as LaminasClient;

/**
 * Manage updates from Queue for order info
 */
abstract class BaseClient
{
    /**
     * Initializes the class to ensure attributes are set up correctly.
     *
     * @param LaminasClient $httpClient The HTTP client used to make requests
     *
     * @return void
     */
    abstract public static function initialize(?LaminasClient $httpClient = null) : void;

    /**
     * Returns the URL being used for queue iteration during runtime
     *
     * @return void
     */
    abstract public static function getQueueUrl() : string;

    /**
     * Returns messages requested from the Queue URL. This will continue to return messages when called until
     * the ueue has been iterated through.
     *
     * @return mixed[]|null Message array for data
     */
    abstract public static function getMessages() : ?array;

    /**
     * Process results to check if any errors exist
     *
     * @param mixed[] $responseArray  The array resulting from decoding the JSON response
     * @param string  $responseString The string representing the body of the response
     *
     * @return void
     */
    abstract protected static function processResultErrors(array $responseArray, string $responseString) : void;

    /**
     * Returns a formatted array of messages from the queue given the result output
     *
     * @param mixed[] $responseArray The decoded JSON array response from SQS
     *
     * @return mixed[]|null
     */
    abstract protected static function parseResponseMessages(array $responseArray) : ?array;
}
