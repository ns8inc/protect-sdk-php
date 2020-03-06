<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Templates;

use NS8\ProtectSDK\Http\Client as HttpClient;
use RuntimeException;
use stdClass;
use function array_merge;
use function http_build_query;
use function in_array;
use function sprintf;

/**
 * Client for interacting with the NS8 Template Service
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
     * Get a template from the NS8 Template Service.
     *
     * @param string        $view           The template name (view) to get
     * @param string        $orderId        The order ID
     * @param string        $token          The access token
     * @param string        $verificationId The customer verification ID
     * @param string        $returnUri      The URI to which the user should be returned (variables get interpolated)
     * @param string[]|null $postParams     Extra POST parameters to send (if null, a GET request will be sent instead)
     *
     * @return stdClass The template
     *
     * @throws RuntimeException If an invalid view is specified.
     */
    public static function get(
        string $view,
        string $orderId,
        string $token,
        string $verificationId,
        string $returnUri,
        ?array $postParams = null
    ) : stdClass {
        if (! in_array($view, self::VALID_VIEWS)) {
            throw new RuntimeException(sprintf('Invalid view "%s" specified', $view));
        }

        $params = [
            'orderId' => $orderId,
            'returnUri' => $returnUri,
            'token' => $token,
            'verificationId' => $verificationId,
            'view' => $view,
        ];

        if (isset($postParams)) {
            return self::getHttpClient()->post(self::TEMPLATE_ENDPOINT, array_merge($params, $postParams));
        }

        return self::getHttpClient()->get(sprintf('%s?%s', self::TEMPLATE_ENDPOINT, http_build_query($params)));
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
