<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Http;

use NS8\ProtectSDK\Config\Manager as ConfigManager;
use NS8\ProtectSDK\Http\Client;
use NS8\ProtectSDK\Http\Exceptions\Http as HttpException;
use PHPUnit\Framework\TestCase;
use Zend\Http\Client as ZendClient;
use Zend\Http\Client\Adapter\Exception\RuntimeException as ZendRuntimeException;
use Zend\Http\Client\Adapter\Test as ZendTestAdapter;

/**
 * HTTP Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Http\CLient
 */
class ClientTest extends TestCase
{
    /**
     * Define request types the HTTP client utilizes
     */
    public const GET_REQUEST_TYPE    = 'GET';
    public const POST_REQUEST_TYPE   = 'POST';
    public const PUT_REQUEST_TYPE    = 'PUT';
    public const DELETE_REQUEST_TYPE = 'DELETE';

    /**
     * Test Components to plug in for mock requests
     */
    public const TEST_URI          = '/path';
    public const TEST_AUTH_NAME    = 'NS8_test';
    public const TEST_ACCESS_TOKEN = '123456';
    public const TEST_SESSION_DATA = [
        'acceptLanguage'    => 'en-US,en;q=0.5',
        'id'                => 'd533c19f-71d6-4372-a170-03da69801356',
        'ip'                => '127.0.0.1',
        'user_agent'        => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6)',
    ];

    /**
     * Attribute to track config manager
     *
     * @var ConfigManager $configManager Config manager used to manage settings during tests
     */
    protected static $configManager;

    /**
     * Test the constructor.
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setSessionData
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testConstructor() : void
    {
        $this->assertInstanceOf(Client::class, new Client(null, null, true, null, self::$configManager));
    }

    /**
     * Test GET request
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::get
     * @covers ::getAccessToken
     * @covers ::setSessionData
     * @covers ::executeWithAuth
     * @covers ::executeJsonRequest
     * @covers ::executeRequest
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::info
     */
    public function testGetRequest() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::GET_REQUEST_TYPE);
        $client         = new Client(
            self::TEST_AUTH_NAME,
            self::TEST_ACCESS_TOKEN,
            true,
            $testHttpClient,
            self::$configManager
        );
        $response       = $client->get(self::TEST_URI);

        $this->assertEquals(self::GET_REQUEST_TYPE, $response->request_type);
    }

    /**
     * Test GET request
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getAccessToken
     * @covers ::setSessionData
     * @covers ::getNonJson
     * @covers ::executeRequest
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::info
     */
    public function testNonJsonGetRequest() : void
    {
        $testHttpClient = $this->buildTestNonJsonHttpClient();
        $client         = new Client(
            self::TEST_AUTH_NAME,
            self::TEST_ACCESS_TOKEN,
            true,
            $testHttpClient,
            self::$configManager
        );
        $response       = $client->getNonJson(self::TEST_URI);

        $this->assertEquals('Test Response', $response);
    }

    /**
     * Test GET request for Non-JSON with Exception being thrown
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getAccessToken
     * @covers ::setSessionData
     * @covers ::getNonJson
     * @covers ::executeRequest
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers ::executeJsonRequest
     * @covers ::executeWithAuth
     * @covers ::getAuthUsername
     * @covers ::getSessionData
     * @covers ::post
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::error
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testNonJsonGetRequestException() : void
    {
        $testHttpClient = $this->buildTestNonJsonHttpClient(true);
        $client         = new Client(
            self::TEST_AUTH_NAME,
            self::TEST_ACCESS_TOKEN,
            true,
            $testHttpClient,
            self::$configManager
        );
        $this->expectException(ZendRuntimeException::class);
        $response = $client->getNonJson(self::TEST_URI);
    }

    /**
     * Test POST request
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getAccessToken
     * @covers ::getAuthUsername
     * @covers ::getSessionData
     * @covers ::post
     * @covers ::setSessionData
     * @covers ::executeWithAuth
     * @covers ::executeJsonRequest
     * @covers ::executeRequest
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::info
     */
    public function testPostRequest() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::POST_REQUEST_TYPE);
        $client         = new Client(
            self::TEST_AUTH_NAME,
            self::TEST_ACCESS_TOKEN,
            true,
            $testHttpClient,
            self::$configManager
        );
        $response       = $client->post(self::TEST_URI);

        $this->assertEquals(self::POST_REQUEST_TYPE, $response->request_type);
    }

    /**
     * Test PUT request
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getAccessToken
     * @covers ::put
     * @covers ::setSessionData
     * @covers ::executeWithAuth
     * @covers ::executeJsonRequest
     * @covers ::executeRequest
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::info
     */
    public function testPutRequest() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::PUT_REQUEST_TYPE);
        $client         = new Client(
            self::TEST_AUTH_NAME,
            self::TEST_ACCESS_TOKEN,
            true,
            $testHttpClient,
            self::$configManager
        );
        $response       = $client->put(self::TEST_URI);

        $this->assertEquals(self::PUT_REQUEST_TYPE, $response->request_type);
    }

    /**
     * Test DELETE request
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::delete
     * @covers ::getAccessToken
     * @covers ::setSessionData
     * @covers ::executeWithAuth
     * @covers ::executeJsonRequest
     * @covers ::executeRequest
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::info
     */
    public function testDeleteRequest() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::DELETE_REQUEST_TYPE);
        $client         = new Client(
            self::TEST_AUTH_NAME,
            self::TEST_ACCESS_TOKEN,
            true,
            $testHttpClient,
            self::$configManager
        );
        $response       = $client->delete(self::TEST_URI);

        $this->assertEquals(self::DELETE_REQUEST_TYPE, $response->request_type);
    }

    /**
     * Test get Auth Name functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setAuthUsername
     * @covers ::getAuthUsername
     * @covers ::setAccessToken
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testgetAuthNameFunctionality() : void
    {
        $client = new Client(null, null, false, null, self::$configManager);
        $client->setAuthUsername(self::TEST_AUTH_NAME);
        $this->assertEquals(self::TEST_AUTH_NAME, $client->getAuthUsername());
    }

    /**
     * Test set Auth Name functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setAuthUsername
     * @covers ::getAuthUsername
     * @covers ::setAccessToken
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testsetAuthNameFunctionality() : void
    {
        $client = new Client(null, null, false, null, self::$configManager);
        $client->setAuthUsername(self::TEST_AUTH_NAME);
        $this->assertEquals(self::TEST_AUTH_NAME, $client->getAuthUsername());
    }

    /**
     * Test get Access Token functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setAccessToken
     * @covers ::getAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testGetAccessTokenFunctionality() : void
    {
        $client = new Client(null, null, false, null, self::$configManager);
        $client->setAccessToken(self::TEST_ACCESS_TOKEN);
        $this->assertEquals(self::TEST_ACCESS_TOKEN, $client->getAccessToken());
    }

    /**
     * Test set Access Token functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setAccessToken
     * @covers ::getAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testSetAccessTokenFunctionality() : void
    {
        $client = new Client(null, null, false, null, self::$configManager);
        $client->setAccessToken(self::TEST_ACCESS_TOKEN);
        $this->assertEquals(self::TEST_ACCESS_TOKEN, $client->getAccessToken());
    }

    /**
     * Test get Session Data functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setSessionData
     * @covers ::getSessionData
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testGetSessionDataFunctionality() : void
    {
        $client = new Client(null, null, false, null, self::$configManager);
        $this->assertEquals(null, $client->getSessionData());
        $client->setSessionData(self::TEST_SESSION_DATA);
        $this->assertEquals(self::TEST_SESSION_DATA, $client->getSessionData());
    }

    /**
     * Test set Session Data functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setSessionData
     * @covers ::getSessionData
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testSetSessionDataFunctionality() : void
    {
        $client = new Client(null, null, false, null, self::$configManager);
        $client->setSessionData(self::TEST_SESSION_DATA);
        $this->assertEquals(self::TEST_SESSION_DATA, $client->getSessionData());
    }

    /**
     * Test if a POST request will throw an error if no auth name is provided
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getAccessToken
     * @covers ::getAuthUsername
     * @covers ::getSessionData
     * @covers ::post
     * @covers ::setSessionData
     * @covers ::executeWithAuth
     * @covers ::executeJsonRequest
     * @covers ::executeRequest
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::error
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testNoAccessTokenException() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::POST_REQUEST_TYPE);
        $client         = new Client(self::TEST_AUTH_NAME, null, true, $testHttpClient, self::$configManager);
        $client->setAccessToken('');
        $this->expectException(HttpException::class);
        $response = $client->post(self::TEST_URI);
    }

    /**
     * Test if a POST request will throw an error if no auth name is provided
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::getAccessToken
     * @covers ::getAuthUsername
     * @covers ::getSessionData
     * @covers ::post
     * @covers ::setSessionData
     * @covers ::executeWithAuth
     * @covers ::executeJsonRequest
     * @covers ::executeRequest
     * @covers ::setAccessToken
     * @covers ::setAuthUsername
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\Manager::setValue
     * @covers NS8\ProtectSDK\Config\Manager::validateKeyCanChange
     * @covers NS8\ProtectSDK\Config\ManagerStructure::__construct
     * @covers NS8\ProtectSDK\Config\ManagerStructure::getConfigByFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::readJsonFromFile
     * @covers NS8\ProtectSDK\Config\ManagerStructure::validateInitialConfigData
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     */
    public function testNoAuthException() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::POST_REQUEST_TYPE);
        $client         = new Client(null, self::TEST_ACCESS_TOKEN, true, $testHttpClient, self::$configManager);
        $client->setAuthUsername('');
        $this->expectException(HttpException::class);
        $response = $client->post(self::TEST_URI);
    }

    /**
     * Sets up Config Manager before tests are ran
     *
     * @return void
     */
    public function setUp() : void
    {
        self::$configManager = new ConfigManager(null, null, null, null, null, true);
        self::$configManager->setValue('logging.api.enabled', false);
    }

    /**
     * Returns a test Zend HTTP client to utilize when invoking the NS8 Core HTTP Client
     *
     * @param string $requestType Request type being sent
     *
     * @return ZendClient
     */
    protected function buildTestHttpClient(string $requestType) : ZendClient
    {
        $adapter        = new ZendTestAdapter();
        $testHttpClient = new ZendClient(self::TEST_URI, ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: application/json' . "\n\n" .
        '{' .
        '   "request_type": "' . $requestType . '",' .
        '   "success": true' .
        "}\n";

        $adapter->setResponse($response);

        return $testHttpClient;
    }

    /**
     * Returns a test Zend HTTP client to utilize when invoking the NS8 Core HTTP Client
     * for Non-JSON requests
     *
     * @param bool $triggerFailure Sets if the HTTP client should fail in the request
     *
     * @return ZendClient
     */
    protected function buildTestNonJsonHttpClient(bool $triggerFailure = false) : ZendClient
    {
        $adapter = new ZendTestAdapter();
        $adapter->setNextRequestWillFail($triggerFailure);
        $testHttpClient = new ZendClient(self::TEST_URI, ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: text/html' . "\n\n" .
        'Test Response';

        $adapter->setResponse($response);

        return $testHttpClient;
    }
}
