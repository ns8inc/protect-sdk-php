<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Merchants;

use NS8\ProtectSDK\Http\Client as HttpClient;
use stdClass;

/**
 * Client for interacting with Merchants
 */
class Client extends BaseClient
{
    /**
     * HTTP Client used to make API requests
     *
     * @var HttpClient
     */
    protected static $httpClient;

    /**
     * Get the current merchant.
     *
     * @return stdClass The current merchant
     */
    public static function getCurrent() : stdClass
    {
        return self::getHttpClient()->get(self::CURRENT_MERCHANT_ENDPOINT);
    }

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
    public static function setHttpClient(HttpClient $httpClient) : void
    {
        self::$httpClient = $httpClient;
    }
}
