<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Queue;

use NS8\ProtectSDK\Queue\Exceptions\Decoding as DecodingException;
use NS8\ProtectSDK\Queue\Exceptions\Response as ResponseException;
use Zend\Http\Client as ZendClient;
use function array_key_exists;
use function json_decode;
use function json_last_error;
use function sprintf;

/**
 * Manage updates from Queue for order info
 */
class Client extends BaseClient
{
    /**
     * Attribute to track URL for connecting to queue
     *
     * @var string $url
     */
    protected static $url;

    /**
     * Attribute to track HTTTP Client used for sending requests
     *
     * @var ZendClient $httpClient
     */
    protected static $httpClient;

    /**
     * SQS Request Constants
     */
    public const DEFAULT_SQS_REQUEST_TYPE = 'GET';

    /**
     * Error keys used when receiving errors from SQS
     */
    public const ERROR_KEY         = 'Error';
    public const ERROR_CODE_KEY    = 'Code';
    public const ERROR_MESSAGE_KEY = 'Message';

    /**
     * Message keys used when parsing messages from SQS
     */
    public const RECEIVE_MESSAGE_RESPONSE_KEY = 'ReceiveMessageResponse';
    public const RECEIVE_MESSAGE_RESULT_KEY   = 'ReceiveMessageResult';
    public const RECEIVE_MESSAGE_SET_KEY      = 'messages';

    /**
     * Available message actions that can be present in a message
     */
    public const MESSAGE_ACTION_UPDATE_EQ8_SCORE          = 'UPDATE_EQ8_SCORE_EVENT';
    public const MESSAGE_ACTION_UPDATE_ORDER_RISK_EVENT   = 'UPDATE_ORDER_RISK_EVENT';
    public const MESSAGE_ACTION_UPDATE_ORDER_STATUS_EVENT = 'UPDATE_ORDER_STATUS_EVENT';

    /**
     * Static headers that we are required to send in SQS requests
     */
    public const REQUIRED_HEADERS = [
        'Accept'    => 'application/json',
        'Host'      => 'sqs.us-west-2.amazonaws.com',
    ];

    /**
     * Initializes the class to ensure attributes are set up correctly.
     *
     * @param ZendClient $httpClient The HTTP client used to make requests
     * @param string     $queueUrl   URL used to access queue
     *
     * @return void
     */
    public static function initialize(?ZendClient $httpClient = null, ?string $queueUrl = null) : void
    {
        self::$url        = $queueUrl;
        self::$httpClient = $httpClient ?? new ZendClient();
    }

    /**
     * Returns the URL being used for queue iteration during runtime
     *
     * @return string The URL to be used for queue connection
     */
    public static function getQueueUrl() : string
    {
        if (! empty(self::$url)) {
            return self::$url;
        }

        //TODO: This MUST be updated once queue URL lambda is in-place
        self::$url = '';

        return self::$url;
    }

    /**
     * Returns messages requested from the Queue URL. This will continue to return messages when called until
     * the ueue has been iterated through.
     *
     * @return mixed[]|null Message array for data
     */
    public static function getMessages() : ?array
    {
        self::$httpClient->resetParameters();
        self::$httpClient->setHeaders(self::REQUIRED_HEADERS);
        self::$httpClient->setMethod(self::DEFAULT_SQS_REQUEST_TYPE);
        self::$httpClient->setUri(self::getQueueUrl());

        $responseString = self::$httpClient->send()->getBody();
        $response       = json_decode($responseString, true);
        self::processResultErrors((array) $response, $responseString);

        return self::parseResponseMessages($response);
    }

    /**
     * Process results to check if any errors exist
     *
     * @param mixed[] $response       The array resulting from decoding the JSON response
     * @param string  $responseString The string representing the body of the response
     *
     * @return void
     *
     * @throws DecodingException
     * @throws ResponseException
     */
    protected static function processResultErrors(array $response, string $responseString) : void
    {
        if (empty($response)) {
            throw new DecodingException(
                sprintf('Unable to decode JSON: %s. ERROR: %s', $responseString, json_last_error())
            );
        }

        if (array_key_exists(self::ERROR_KEY, $response)) {
            $errorData = $response[self::ERROR_KEY];
            throw new ResponseException(
                sprintf('%s:%s', $errorData[self::ERROR_CODE_KEY], $errorData[self::ERROR_MESSAGE_KEY])
            );
        }
    }

    /**
     * Returns a formatted array of messages from the queue given the result output
     *
     * @param mixed[] $response The decoded JSON array response from SQS
     *
     * @return mixed[]
     */
    protected static function parseResponseMessages(array $response) : ?array
    {
        $messages =
        $response[self::RECEIVE_MESSAGE_RESPONSE_KEY][self::RECEIVE_MESSAGE_RESULT_KEY][self::RECEIVE_MESSAGE_SET_KEY];
        if (empty($messages)) {
            return null;
        }

        $resultArray = [];
        foreach ($messages as $messageData) {
            $attributes = [];
            foreach ((array) $messageData['MessageAttributes'] as $attributeData) {
                $attributes[$attributeData['Name']] = $attributeData['Value']['StringValue'];
            }

            $resultArray[] = [
                'attributes' => $attributes,
                'body' => $messageData['Body'],
                'message_id' => $messageData['MessageId'],
            ];
        }

        return $resultArray;
    }
}
