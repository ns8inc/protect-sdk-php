<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Http;

use NS8\ProtectSDK\Http\Client;
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
     * Test Components to plug in for mock requests
     */
    public const TEST_URI          = 'https://ns8.com';
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
     */
    public function testGetRequest() : void
    {
        $testHttpClient = $this->buildTestHttpClient(Client::GET_REQUEST_TYPE);
        $client         = new Client(self::TEST_AUTH_NAME, self::TEST_ACCESS_TOKEN, true, $testHttpClient);
        $response       = $client->get(self::TEST_URI);

        $this->assertEquals($response->request_type, Client::GET_REQUEST_TYPE);
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

        $this->assertEquals($response->request_type, Client::POST_REQUEST_TYPE);
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

        $this->assertEquals($response->request_type, Client::PUT_REQUEST_TYPE);
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

        $this->assertEquals($response->request_type, Client::DELETE_REQUEST_TYPE);
    }

    /**
     * Test Auth Name functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setAuthUsername
     * @covers ::getAuthUsername
     */
    public function testAuthNameFunctionality() : void
    {
        $client = new Client(null, null, false);
        $client->setAuthUsername(self::TEST_AUTH_NAME);
        $this->assertEquals($client->getAuthUsername(), self::TEST_AUTH_NAME);
    }

    /**
     * Test Auth Name functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setAccessToken
     * @covers ::getAccessToken
     */
    public function testAccessTokenFunctionality() : void
    {
        $client = new Client(null, null, false);
        $client->setAccessToken(self::TEST_ACCESS_TOKEN);
        $this->assertEquals($client->getAccessToken(), self::TEST_ACCESS_TOKEN);
    }

    /**
     * Test Auth Name functionality
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::setSessionData
     * @covers ::getSessionData
     */
    public function testSessionDataFunctionality() : void
    {
        $client = new Client(null, null, false);
        $client->setSessionData(self::TEST_SESSION_DATA);
        $this->assertEquals($client->getSessionData(), self::TEST_SESSION_DATA);
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

        $response =  'HTTP/1.1 200 OK' . "\r\n" .
        'Content-type: application/json' . "\r\n\r\n" .
        '{' .
        '   "request_type": "' . $requestType . '",' .
        '   "success": true' .
        "}\r\n";

        $adapter->setResponse($response);

        return $testHttpClient;
    }
}
