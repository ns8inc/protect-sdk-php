<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Http;

use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Http\Exceptions\Http as HttpException;
use NS8\ProtectSDK\Logging\Client as LoggingClient;
use stdClass;
use Throwable;
use Zend\Http\Client as ZendClient;
use Zend\Http\PhpEnvironment\RemoteAddress as ZendRemoteAddress;
use Zend\Http\PhpEnvironment\Request as ZendRequest;
use Zend\Json\Decoder as ZendJsonDecoder;
use function array_merge;
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
     * HTTP Library Client attribute
     *
     * @var Zend\Http\Client $client
     */
    protected $client;

    /**
     * Attribute to permit auth user
     *
     * @var string $authUsername
     */
    protected $authUsername;

    /**
     * Attribute to permit access token usage
     *
     * @var string $accessToken
     */
    protected $accessToken;

    /**
     * Attribute to track session data to be sent in requests
     *
     * @var mixed[] $sessionData
     */
    protected $sessionData;

    /**
     * Config manager to fetch HTTP config info
     */
    protected $configManager;

    /**
     * Constructor for HTTP Client
     *
     * @param ?string     $authUsername   Authentication username for NS8 requests
     * @param ?string     $accessToken    Access Token for NS8 requests
     * @param bool        $setSessionData Determines if the class instance should set session data to pass to NS8
     * @param ?ZendClient $client         HTTP client to use when making requests
     */
    public function __construct(
        ?string $authUsername = null,
        ?string $accessToken = null,
        bool $setSessionData = true,
        ?ZendClient $client = null
    ) {
        $this->authUsername  = $authUsername;
        $this->accessToken   = $accessToken;
        $this->client        = $client ?? new ZendClient();
        $this->configManager = new ConfigManager(null, null, null, null, null, true);

        $accessToken = $accessToken ?? $this->configManager->getEnvValue('authorization.access_token');
        if (! empty($accessToken)) {
            $this->setAccessToken($accessToken);
        }

        $authUsername = $authUsername ?? $this->configManager->getEnvValue('authorization.auth_user');
        if (! empty($authUsername)) {
            $this->setAuthUsername($authUsername);
        }

        if (! $setSessionData) {
            return;
        }

        $requestContext = new ZendRequest();
        $this->setSessionData([
            'acceptLanguage' => $requestContext->getHeaders()->get('Accept-Language'),
            // ToDo: Determine how we should add Customer Session Id
            // 'id' => $this->customerSession->getSessionId(),
            'ip' => (new ZendRemoteAddress())->getIpAddress(),
            'userAgent' => $requestContext->getHeaders()->get('User-Agent'),
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
     * Sends GET requests to NS8 services for retrieving information
     *
     * @param string  $url        URL that is being accessed
     * @param mixed[] $parameters Parameters to include in request
     * @param mixed[] $headers    Array of heads to include in request
     * @param int     $timeout    Timeout length for the request
     *
     * @return string
     */
    public function getNonJson(
        string $url,
        array $parameters = [],
        array $headers = [],
        int $timeout = self::DEFAULT_TIMEOUT_VALUE
    ) : string {
        try {
            return $this->executeRequest($url, [], self::GET_REQUEST_TYPE, $parameters, $headers, $timeout);
        } catch (Throwable $t) {
            $logger = new LoggingClient();
            $logger->error('Non-JSONHTTP call failed', $t, ['url' => $url, 'data' => $data, 'parameters' => $parameters, 'headers' => $headers, 'timeout' => $timeout]);
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
        if (empty($authUsername)) {
            throw new HttpException(
                sprintf('An auth username is required for NS8 %s requests.', self::POST_REQUEST_TYPE)
            );
        }

        $data['session']  = $this->getSessionData();
        $data['username'] = $this->getAuthUsername();

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
            if (empty($accessToken)) {
                throw new HttpException('An authorization token is required for NS8 requests');
            }

            $authHeaderString = sprintf(self::AUTH_STRING_HEADER_FORMAT, $accessToken);
            $authHeader       = ['Authorization' => $authHeaderString];
            $allHeaders       = array_merge($headers, $authHeader);

            return $this->executeJsonRequest($url, $data, $method, $parameters, $allHeaders, $timeout);
        } catch (Throwable $t) {
            $logger = new LoggingClient();
            $logger->error('HTTP call failed', $t, ['url' => $url, 'data' => $data, 'parameters' => $parameters, 'headers' => $headers, 'timeout' => $timeout]);
            throw $t;
        }
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

        $this->client->setUri($uri);
        $this->client->setOptions(['timeout' => $timeout]);
        $this->client->setMethod($method);
        $this->client->setParameterGet($parameters);
        // TODO: Implement protect version once configuration logic is in place
        //$headers['extension-version'] = $this->config->getProtectVersion();
        if (! empty($headers)) {
            $this->client->setHeaders($headers);
        }

        if (! empty($data)) {
            $this->client->setParameterPost($data);
        }

        $response = $this->client->send()->getBody();

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
