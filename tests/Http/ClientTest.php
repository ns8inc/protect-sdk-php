<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Http;

use NS8\ProtectSDK\Http\Client;
use NS8\ProtectSDK\Http\Exceptions\Http as HttpException;
use PHPUnit\Framework\TestCase;
use Zend\Http\Client as ZendClient;
use Zend\Http\Client\Adapter\Test as ZendTestAdapter;

/**
 * HTTP Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Http\Client
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
     * Test the constructor.
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setSessionData
     */
    public function testConstructor() : void
    {
        $this->assertInstanceOf(Client::class, new Client());
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
     */
    public function testGetRequest() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::GET_REQUEST_TYPE);
        $client         = new Client(self::TEST_AUTH_NAME, self::TEST_ACCESS_TOKEN, true, $testHttpClient);
        $response       = $client->get(self::TEST_URI);

        $this->assertEquals(self::GET_REQUEST_TYPE, $response->request_type);
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
     * @covers ::getNonJson
     * @covers ::executeRequest
     */
    public function testNonJsonGetRequest() : void
    {
        $testHttpClient = $this->buildTestNonJsonHttpClient(Client::GET_REQUEST_TYPE);
        $client         = new Client(self::TEST_AUTH_NAME, self::TEST_ACCESS_TOKEN, true, $testHttpClient);
        $response       = $client->getNonJson(self::TEST_URI);

        $this->assertEquals('Test Response', $response);
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
     */
    public function testPostRequest() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::POST_REQUEST_TYPE);
        $client         = new Client(self::TEST_AUTH_NAME, self::TEST_ACCESS_TOKEN, true, $testHttpClient);
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
     */
    public function testPutRequest() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::PUT_REQUEST_TYPE);
        $client         = new Client(self::TEST_AUTH_NAME, self::TEST_ACCESS_TOKEN, true, $testHttpClient);
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
     */
    public function testDeleteRequest() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::DELETE_REQUEST_TYPE);
        $client         = new Client(self::TEST_AUTH_NAME, self::TEST_ACCESS_TOKEN, true, $testHttpClient);
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
     */
    public function testgetAuthNameFunctionality() : void
    {
        $client = new Client(null, null, false);
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
     */
    public function testsetAuthNameFunctionality() : void
    {
        $client = new Client(null, null, false);
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
     */
    public function testGetAccessTokenFunctionality() : void
    {
        $client = new Client(null, null, false);
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
     */
    public function testSetAccessTokenFunctionality() : void
    {
        $client = new Client(null, null, false);
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
     */
    public function testGetSessionDataFunctionality() : void
    {
        $client = new Client(null, null, false);
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
     */
    public function testSetSessionDataFunctionality() : void
    {
        $client = new Client(null, null, false);
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
     */
    public function testNoAccessTokenException() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::POST_REQUEST_TYPE);
        $client         = new Client(self::TEST_AUTH_NAME, null, true, $testHttpClient);
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
     */
    public function testNoAuthException() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::POST_REQUEST_TYPE);
        $client         = new Client(null, self::TEST_ACCESS_TOKEN, true, $testHttpClient);
        $client->setAuthUsername('');
        $this->expectException(HttpException::class);
        $response = $client->post(self::TEST_URI);
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
     * @return ZendClient
     */
    protected function buildTestNonJsonHttpClient() : ZendClient
    {
        $adapter        = new ZendTestAdapter();
        $testHttpClient = new ZendClient(self::TEST_URI, ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: text/html' . "\n\n" .
        'Test Response';

        $adapter->setResponse($response);

        return $testHttpClient;
    }
}
