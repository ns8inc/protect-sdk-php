<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Http;

use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Http\Exceptions\Http as HttpException;
use NS8\ProtectSDK\Logging\Client as LoggingClient;
use NS8\ProtectSDK\Logging\Handlers\Api as ApiHandler;
use NS8\ProtectSDK\Security\Client as SecurityClient;
use stdClass;
use Throwable;
use Zend\Http\Client as ZendClient;
use Zend\Http\PhpEnvironment\RemoteAddress as ZendRemoteAddress;
use Zend\Json\Decoder as ZendJsonDecoder;
use function array_merge;
use function in_array;
use function sprintf;

/**
 * HTTP/Rest client that allows the NS8 SDK to communicate with NS8 services
 */
class Client implements IProtectClient
{
    /**
     * Define request types the HTTP client utilizes
     */
    public const GET_REQUEST_TYPE    = 'GET';
    public const POST_REQUEST_TYPE   = 'POST';
    public const PUT_REQUEST_TYPE    = 'PUT';
    public const DELETE_REQUEST_TYPE = 'DELETE';

    /**
     * API prefix for NS8 Client routes
     */
    public const ROUTE_PREFIX = '/api';

    /**
     * Default HTTP Request Timeout Value
     */
    public const DEFAULT_TIMEOUT_VALUE = 30;

    /**
     * Format for Authorization header value
     */
    public const AUTH_STRING_HEADER_FORMAT = 'Bearer %s';

    /**
     * A list of paths we should not log HTTP requests for
     */
    public const EXCLUDED_LOG_PATHS = [ApiHandler::LOGGING_PATH];

    /**
     * HTTP Library Client attribute
     *
     * @var Zend\Http\Client
     */
    protected $client;

    /**
     * Attribute to permit auth user
     *
     * @var string
     */
    protected $authUsername;

    /**
     * Attribute to permit access token usage
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Attribute to track session data to be sent in requests
     *
     * @var mixed[]
     */
    protected $sessionData;

    /**
     * Config manager to fetch HTTP config info
     *
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * Logging client to log data
     *
     * @var LoggingClient
     */
    protected $loggingClient;

    /**
     * Constructor for HTTP Client
     *
     * @param ?string        $authUsername   Authentication username for NS8 requests
     * @param ?string        $accessToken    Access Token for NS8 requests
     * @param bool           $setSessionData Determines if the class instance should set session data to pass to NS8
     * @param ?ZendClient    $client         HTTP client to use when making requests
     * @param ?ConfigManager $configManager  Configuration Manager used by the client for fetching request info
     * @param ?LoggingClient $loggingClient  Logging client used for recording request data
     */
    public function __construct(
        ?string $authUsername = null,
        ?string $accessToken = null,
        bool $setSessionData = true,
        ?ZendClient $client = null,
        ?ConfigManager $configManager = null,
        ?LoggingClient $loggingClient = null
    ) {
        $this->client        = $client ?? new ZendClient();
        $this->configManager = $configManager ?? new ConfigManager();
        $this->loggingClient = $loggingClient ?? new LoggingClient();

        $this->configManager::initConfiguration();
        $accessToken = $accessToken ?? SecurityClient::getNs8AccessToken();
        if (! empty($accessToken)) {
            $this->setAccessToken($accessToken);
        }

        $authUsername = $authUsername ?? SecurityClient::getAuthUser();
        if (! empty($authUsername)) {
            $this->setAuthUsername($authUsername);
        }

        if (! $setSessionData) {
            return;
        }

        $this->setSessionData([
            'ip' => (new ZendRemoteAddress())->getIpAddress(),
            'acceptLanguage' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null,
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

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
        int $timeout = self::DEFAULT_TIMEOUT_VALUE
    ) : stdClass {
        return $this->executeWithAuth($url, [], self::GET_REQUEST_TYPE, $parameters, $headers, $timeout);
    }

    /**
     * Sends requests to NS8 services for retrieving information
     *
     * @param string  $url        URL that is being accessed
     * @param string  $method     HTTP request method to be used
     * @param mixed[] $parameters Parameters to include in request
     * @param mixed[] $headers    Array of heads to include in request
     * @param int     $timeout    Timeout length for the request
     *
     * @return string
     */
    public function sendNonObjectRequest(
        string $url,
        string $method = self::POST_REQUEST_TYPE,
        array $parameters = [],
        array $headers = [],
        int $timeout = self::DEFAULT_TIMEOUT_VALUE
    ) : string {
        try {
            $accessToken = $this->getAccessToken();
            if (! SecurityClient::validateNs8AccessToken($accessToken)) {
                throw new HttpException('An authorization token is required for NS8 requests');
            }
            $authHeaderString = sprintf(self::AUTH_STRING_HEADER_FORMAT, $accessToken);
            $authHeader       = ['Authorization' => $authHeaderString];
            $headers          = array_merge($headers, $authHeader);

            return $this->executeRequest(
                $url,
                [],
                $method,
                $parameters,
                $headers,
                $timeout
            );
        } catch (Throwable $t) {
            $errorData = ['url' => $url,'parameters' => $parameters, 'headers' => $headers, 'timeout' => $timeout];
            $this->loggingClient->error('Non-JSONHTTP call failed', $t, $errorData);
            throw $t;
        }
    }

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
        int $timeout = self::DEFAULT_TIMEOUT_VALUE
    ) : stdClass {
        $authUsername = $this->getAuthUsername();
        if (! SecurityClient::validateAuthUser($authUsername)) {
            throw new HttpException(
                sprintf('An auth username is required for NS8 %s requests.', self::POST_REQUEST_TYPE)
            );
        }

        $sessionData      = $data['session'] ?? [];
        $data['session']  = array_merge((array) $this->getSessionData(), $sessionData);
        $data['username'] = $authUsername;

        return $this->executeWithAuth($url, $data, self::POST_REQUEST_TYPE, $parameters, $headers, $timeout);
    }

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
        int $timeout = self::DEFAULT_TIMEOUT_VALUE
    ) : stdClass {
        return $this->executeWithAuth($url, $data, self::PUT_REQUEST_TYPE, $parameters, $headers, $timeout);
    }

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
        int $timeout = self::DEFAULT_TIMEOUT_VALUE
    ) : stdClass {
        return $this->executeWithAuth($url, $data, self::DELETE_REQUEST_TYPE, $parameters, $headers, $timeout);
    }

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
    protected function executeWithAuth(
        string $url,
        array $data,
        string $method = self::POST_REQUEST_TYPE,
        array $parameters = [],
        array $headers = [],
        int $timeout = self::DEFAULT_TIMEOUT_VALUE
    ) : stdClass {
        try {
            $accessToken = $this->getAccessToken();
            if (! SecurityClient::validateNs8AccessToken($accessToken)) {
                throw new HttpException('An authorization token is required for NS8 requests');
            }

            $authHeaderString = sprintf(self::AUTH_STRING_HEADER_FORMAT, $accessToken);
            $authHeader       = ['Authorization' => $authHeaderString];
            $allHeaders       = array_merge($headers, $authHeader);

            return $this->executeJsonRequest($url, $data, $method, $parameters, $allHeaders, $timeout);
        } catch (Throwable $t) {
            $errorData = [
                'url' => $url,
                'data' => $data,
                'parameters' => $parameters,
                'headers' => $headers,
                'timeout' => $timeout,
            ];
            $this->loggingClient->error('HTTP call failed', $t, $errorData);
            throw $t;
        }

        return new stdClass();
    }

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
     * @return string
     */
    protected function executeRequest(
        string $route,
        array $data = [],
        string $method = self::POST_REQUEST_TYPE,
        array $parameters = [],
        array $headers = [],
        int $timeout = self::DEFAULT_TIMEOUT_VALUE
    ) : string {
        $response = null;
        $baseUri  = $this->configManager->getEnvValue('urls.client_url');
        $uri      = $baseUri . self::ROUTE_PREFIX . $route;

        // For non
        $accessToken      = $this->getAccessToken();
        $authHeaderString = sprintf(self::AUTH_STRING_HEADER_FORMAT, $accessToken);
        $authHeader       = ['Authorization' => $authHeaderString];
        $allHeaders       = array_merge($headers, $authHeader);

        $this->client->setUri($uri);
        $this->client->setOptions(['timeout' => $timeout]);
        $this->client->setMethod($method);
        $this->client->setParameterGet($parameters);
        $headers['extension-version'] = $this->configManager->getValue('version');
        if (! empty($headers)) {
            $this->client->setHeaders($headers);
        }

        if (! empty($data)) {
            $this->client->setParameterPost($data);
        }

        $response = $this->client->send()->getBody();
        if ($this->configManager->getValue('logging.record_all_http_calls') &&
        ! in_array($route, self::EXCLUDED_LOG_PATHS)) {
            $data = [
                'url' => $uri,
                'data' => $data,
                'method' => $method,
                'parameters' => $parameters,
                'headers' => $headers,
                'timeout' => $timeout,
                'response' => $response,
            ];
            $this->loggingClient->info('HTTP Request sent', $data);
        }

        // Reset all attributes of client after the request
        $this->client->resetParameters(true);

        return $response;
    }

    /**
     * Return a formatted JSON request response
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
    protected function executeJsonRequest(
        string $route,
        array $data = [],
        string $method = self::POST_REQUEST_TYPE,
        array $parameters = [],
        array $headers = [],
        int $timeout = self::DEFAULT_TIMEOUT_VALUE
    ) : stdClass {
        $body = $this->executeRequest($route, $data, $method, $parameters, $headers, $timeout);

        return ZendJsonDecoder::decode($body);
    }

    /**
     * Sets session data intended to be passed to NS8 services
     *
     * @param mixed[] $sessionData Session data being set for request
     *
     * @return IProtectClient
     */
    public function setSessionData(array $sessionData) : IProtectClient
    {
        $this->sessionData = $sessionData;

        return $this;
    }

    /**
     * Returns session data used in HTTP requests to NS8 services
     *
     * @return mixed[]|null
     */
    public function getSessionData() : ?array
    {
        return $this->sessionData;
    }

    /**
     * Set authusername used in post requests
     *
     * @param srtring $authUsername Authentication username to use when sending requests
     *
     * @return IProtectClient
     */
    public function setAuthUsername(string $authUsername) : IProtectClient
    {
        $this->authUsername = $authUsername;

        return $this;
    }

    /**
     * Return authusername for post requests
     *
     * @return string|null
     */
    public function getAuthUsername() : ?string
    {
        return $this->authUsername;
    }

    /**
     * Set access token used in requests with authentication
     *
     * @param string $accessToken Authentication username to use when sending requests
     *
     * @return IProtectClient
     */
    public function setAccessToken(string $accessToken) : IProtectClient
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Return access token used in requests with authentication
     *
     * @return string|null
     */
    public function getAccessToken() : ?string
    {
        return $this->accessToken;
    }
}
