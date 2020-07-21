<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Queue;

use Laminas\Http\Client as LaminasClient;
use NS8\ProtectSDK\Http\Client as NS8HttpClient;
use NS8\ProtectSDK\Queue\Exceptions\Decoding as DecodingException;
use NS8\ProtectSDK\Queue\Exceptions\Response as ResponseException;
use function array_key_exists;
use function http_build_query;
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
     * @var LaminasClient $httpClient
     */
    protected static $httpClient;

     /**
      * Attribute to track HTTTP Client used for sending requests
      *
      * @var NS8HttpClient
      */
    protected static $ns8HttpClient;

    /**
     * SQS Request Constants
     */
    public const DEFAULT_SQS_REQUEST_TYPE = 'GET';

    /**
     * URLs to manage SQS information through Protect
     */
    public const GET_QUEUE_URL            = '/polling/GetQueueUrl';
    public const DELETE_QUEUE_MESSAGE_URL = '/polling/DeleteQueueMessage';

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
    public const REQUIRED_HEADERS = ['Accept' => 'application/json'];

    /**
     * Initializes the class to ensure attributes are set up correctly.
     *
     * @param LaminasClient $httpClient The HTTP client used to make requests
     * @param string        $queueUrl   URL used to access queue
     *
     * @return void
     */
    public static function initialize(?LaminasClient $httpClient = null, ?string $queueUrl = null) : void
    {
        self::$url        = $queueUrl;
        self::$httpClient = $httpClient ?? new LaminasClient();
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

        $urlData   = self::getNs8HttpClient()->post(self::GET_QUEUE_URL);
        self::$url = $urlData->url;

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
     * Deletes a message from the queue.
     *
     * @param string $receiptHandle The receipt handle of the message we want to delete
     *
     * @return bool Returns true if the API call to delete the message was successful otherwise false
     */
    public static function deleteMessage(string $receiptHandle) : bool
    {
        $deleteUrl    = self::DELETE_QUEUE_MESSAGE_URL . '?' . http_build_query(['receiptHandle' => $receiptHandle]);
        $deleteResult = self::getNs8HttpClient()->post($deleteUrl);

        return $deleteResult->success;
    }

    /**
     * Returns the NS8 HTTP client to be used for making API requests
     *
     * @return NS8HttpClient the client to be used
     */
    public static function getNs8HttpClient() : NS8HttpClient
    {
        self::$ns8HttpClient = self::$ns8HttpClient ?? new NS8HttpClient();

        return self::$ns8HttpClient;
    }

    /**
     * Sets an explicit NS8 HTTP client for making API requests
     *
     * @param NS8HttpClient $ns8HttpClient The client we are passing in to make requests
     *
     * @return void
     */
    public static function setNs8HttpClient(NS8HttpClient $ns8HttpClient) : void
    {
        self::$ns8HttpClient = $ns8HttpClient;
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
            $messageBody = json_decode($messageData['Body'], true);
            if (empty($messageBody)) {
                throw new DecodingException('Emmpty message body received from Queue.');
            }
            $messageBody['receipt_handle'] = $messageData['ReceiptHandle'];
            $resultArray[]                 = $messageBody;
        }

        return $resultArray;
    }
}
