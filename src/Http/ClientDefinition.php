<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Http;

use stdClass;

/**
 * HTTP/Rest client interface that structures how the NS8 SDK will communicate with NS8 services
 */
interface ClientDefinition
{
    /**
     * Sends GET requests to NS8 services for retrieving information
     *
     * @param string  $url        URL that is being accessed
     * @param mixed[] $parameters Parameters to include in request
     * @param mixed[] $headers    Array of heads to include in request
     * @param int     $timeout    Timeout length for the request
     *
     * @return stdClass
     */
    public function get(
        string $url,
        array $parameters = [],
        array $headers = [],
        int $timeout = 30
    ) : stdClass;

    /**
     * Sends POST requests to NS8 services to create new records
     *
     * @param string  $url        URL that is being accessed
     * @param mixed[] $data       Data to include in body of the request
     * @param mixed[] $parameters Parameters to include in request
     * @param mixed[] $headers    Array of heads to include in request
     * @param int     $timeout    Timeout length for the request
     *
     * @return stdClass
     */
    public function post(
        string $url,
        array $data = [],
        array $parameters = [],
        array $headers = [],
        int $timeout = 30
    ) : stdClass;

    /**
     * Sends PUT requests to NS8 services to update existing records
     *
     * @param string  $url        URL that is being accessed
     * @param mixed[] $data       Data to include in body of the request
     * @param mixed[] $parameters Parameters to include in request
     * @param mixed[] $headers    Array of heads to include in request
     * @param int     $timeout    Timeout length for the request
     *
     * @return stdClass
     */
    public function put(
        string $url,
        array $data = [],
        array $parameters = [],
        array $headers = [],
        int $timeout = 30
    ) : stdClass;

    /**
     * Sends DELETE requests to NS8 services to remove records
     *
     * @param string  $url        URL that is being accessed
     * @param mixed[] $data       Data to include in body of the request
     * @param mixed[] $parameters Parameters to include in request
     * @param mixed[] $headers    Array of heads to include in request
     * @param int     $timeout    Timeout length for the request
     *
     * @return stdClass
     */
    public function delete(
        string $url,
        array $data = [],
        array $parameters = [],
        array $headers = [],
        int $timeout = 30
    ) : stdClass;

    /**
     * Sends requests to NS8 services with authorization credentials in place
     *
     * @param string  $url        URL that is being accessed
     * @param mixed[] $data       Data to include in body of the request
     * @param string  $method     The HTTP method being used to send the request
     * @param mixed[] $parameters Parameters to include in request
     * @param mixed[] $headers    Array of heads to include in request
     * @param int     $timeout    Timeout length for the request
     *
     * @return stdClass
     */
    public function executeWithAuth(
        string $url,
        array $data,
        string $method = 'POST',
        array $parameters = [],
        array $headers = [],
        int $timeout = 30
    ) : stdClass;

    /**
     * Sends requests to NS8 services
     *
     * @param string  $route      URL that is being accessed
     * @param mixed[] $data       Data to include in body of the request
     * @param string  $method     The HTTP method being used to send the request
     * @param mixed[] $parameters Parameters to include in request
     * @param mixed[] $headers    Array of heads to include in request
     * @param int     $timeout    Timeout length for the request
     *
     * @return stdClass
     */
    public function executeRequest(
        string $route,
        array $data = [],
        string $method = 'POST',
        array $parameters = [],
        array $headers = [],
        int $timeout = 30
    ) : string;

    /**
     * Sets session data intended to be passed to NS8 services
     *
     * @param mixed[] $sessionData Session data being set for request
     *
     * @return void
     */
    public function setSessionData(array $sessionData) : void;

    /**
     * Returns session data used in HTTP requests to NS8 services
     *
     * @return mixed[]
     */
    public function getSessionData() : array;

    /**
     * Set authusername used in post requests
     *
     * @param srtring $authUsername Authentication username to use when sending requests
     *
     * @return void
     */
    public function setAuthUsername(string $authUsername) : void;

    /**
     * Return authusername for post requests
     *
     * @return string|null
     */
    public function getAuthUsername() : ?string;

    /**
     * Set access token used in requests with authentication
     *
     * @param string $accessToken Access token to be used when sending requests
     *
     * @return void
     */
    public function setAccessToken(string $accessToken) : void;

    /**
     * Return access token used in requests with authentication
     *
     * @return string|null
     */
    public function getAccessToken() : ?string;
}
