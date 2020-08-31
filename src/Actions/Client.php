<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Actions;

use NS8\ProtectSDK\Http\Client as HttpClient;

/**
 * Get Wrapper for handling general getActions that are requested
 */
class Client extends BaseClient
{
    /**
     * Response code for successful action setting
     */
    const ACTION_SUCCESS_CODE = 200;

    /**
     * API path for Switch Executor
     */
    const SWITCH_EXECUTOR_PATH = '/switch/executor';

    /**
     * Action key used for switch actions
     */
    const ACTION_KEY = 'action';

    /**
     * HTTP Client used to make API requests
     *
     * @var HttpClient
     */
    protected static $httpClient;

    /**
     * Returns the HTTP client to be used for making API requests
     *
     * @return HttpClient the client to be used
     */
    public static function getHttpClient() : HttpClient
    {
        self::$httpClient = self::$httpClient ?? new HttpClient();

        return self::$httpClient;
    }

     /**
      * Sets an explicit HTTP client for making API requests
      *
      * @param HttpClient $httpClient The client we are passing in to make requests
      *
      * @return void
      */
    public static function setHttpClient(HttpClient $httpClient)
    {
        self::$httpClient = $httpClient;
    }

     /**
      * Get function that serves as a wrapper method for HTTP GET calls to NS8 for fetching such info as an Order Score
      *
      * @param string  $requestType The type of info we are intending to fetch
      * @param mixed[] $data        The data needed for retrieving the requestion information
      *
      * @return mixed Returns the result of the NS8 API call
      */
    public static function getEntity(string $requestType, array $data = [])
    {
        return self::getHttpClient()->get($requestType, $data);
    }

    /**
     * Set function that serves as a wrapper method for HTTP POST calls to NS8 when Actrions
     * are triggered on the client side.
     *
     * @param string  $eventName The event that has occurred to send data to the NS8 API
     * @param mixed[] $data      Data related to the event that has occurred
     *
     * @return bool if the NS8 API set call was completed successfully (true if successful, false otherwise)
     */
    public static function setAction(string $eventName, array $data = []) : bool
    {
        return self::sendProtectData($eventName, $data);
    }

   /**
    * Event trigger function that serves as a wrapper method for HTTP POST calls to NS8 when Events
    * are triggered on the client side.
    *
    * @param string  $eventName The event that has occurred to send data to the NS8 API
    * @param mixed[] $data      Data related to the event that has occurred
    *
    * @return bool if the NS8 API trigger event call was completed successfully (true if successful, false otherwise)
    */
    public static function triggerEvent(string $eventName, array $data = []) : bool
    {
        return self::sendProtectData($eventName, $data);
    }

    /**
     * Sends data for a given action/event to the Protect API
     *
     * @param string  $eventName The event that has occurred to send data to the NS8 API
     * @param mixed[] $data      Data related to the event that has occurred
     *
     * @return bool if the call was completed successfully (true if successful, false otherwise)
     */
    protected static function sendProtectData(string $eventName, array $data = []) : bool
    {
        $result = self::getHttpClient()->post(self::SWITCH_EXECUTOR_PATH, $data, [self::ACTION_KEY => $eventName]);

        return $result->httpCode === self::ACTION_SUCCESS_CODE;
    }
}
