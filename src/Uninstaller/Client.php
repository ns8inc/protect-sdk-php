<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Uninstaller;

use NS8\ProtectSDK\Actions\Client as ActionsClient;
use NS8\ProtectSDK\Http\Client as HttpClient;
use stdClass;

/**
 * Client for uninstalling the NS8 Protect module
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
     * Uninstall the NS8 Protect module.
     *
     * @return stdClass The response from the uninstallation request
     */
    public static function uninstall() : stdClass
    {
        return self::getHttpClient()->post(ActionsClient::SWITCH_EXECUTOR_PATH, [], [
            'action' => ActionsClient::UNINSTALL_ACTION,
        ]);
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
    public static function setHttpClient(HttpClient $httpClient)
    {
        self::$httpClient = $httpClient;
    }
}
