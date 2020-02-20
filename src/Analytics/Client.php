<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Analytics;

use NS8\ProtectSDK\Http\Client as HttpClient;
use function json_decode;

/**
 * Class for dictating general NS8 Analytics components
 */
class Client extends BaseClient
{
    /**
     * POST route for fetching TrueStats script from NS8
     */
    public const TRUE_STATS_ROUTE = '/init/script';

    /**
     * HTTP Client used to make API requests
     *
     * @var HttpClient
     */
    protected static $httpClient;

    /**
     * Returns TrueStats URL
     *
     * @return string Path to TrueStats Script endpoint
     */
    public static function getTrueStatsRoute() : string
    {
        return self::TRUE_STATS_ROUTE;
    }

    /**
     * Returns the TrueStats JavaScript block
     *
     * @return string A string containing TrueStats JavaScript to load on front-end pages
     */
    public static function getTrueStatsScript() : string
    {
        $script = self::getHttpClient()->sendNonObjectRequest(self::getTrueStatsRoute());

        return json_decode($script) ?? '';
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
