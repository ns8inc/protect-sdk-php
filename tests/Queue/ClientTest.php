<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Queue;

use Laminas\Http\Client as LaminasClient;
use Laminas\Http\Client\Adapter\Test as LaminasTestAdapter;
use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Queue\Client as QueueClient;
use NS8\ProtectSDK\Queue\Exceptions\Decoding as DecodingException;
use NS8\ProtectSDK\Queue\Exceptions\Response as ResponseException;
use PHPUnit\Framework\TestCase;
use function json_decode;
use function json_encode;

/**
 * Queue Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Queue\Client
 */
class ClientTest extends TestCase
{
    public const TEST_URI = '/path';

    public const TEST_SUCCESS_RESPONSE = [
        'ReceiveMessageResponse' => [
            'ReceiveMessageResult' => [
                'messages' => [
                    [
                        'Attributes' => null,
                        'Body' => '{"action":"UPDATE_ORDER_STATUS_EVENT","orderId":"1","score":50,"status":"APPROVED"}',
                        'MD5OfBody' =>  '609312bab300febd507aaf43938653c5',
                        'MD5OfMessageAttributes' => null,
                        'MessageAttributes' => [
                            [
                                'Name' => 'order_id',
                                'Value' => [
                                    'BinaryListValues' => null,
                                    'BinaryValue' => null,
                                    'DataType' => 'String',
                                    'StringListValues' => null,
                                    'StringValue' => '123A',
                                ],
                            ],
                        ],
                        'MessageId' => '123456A',
                        'ReceiptHandle' => 'ABCD',
                    ],
                    [
                        'Attributes' => null,
                        'Body' => '{"action":"UPDATE_EQ8_SCORE_EVENT","orderId":"1","score":229,"status":"APPROVED"}',
                        'MD5OfBody' =>  '609312bab300febd507aaf43938653c6',
                        'MD5OfMessageAttributes' => null,
                        'MessageAttributes' => null,
                        'MessageId' => '123456A',
                        'ReceiptHandle' => 'ABCD',
                    ],
                ],
            ],
            'ResponseMetadata' => ['RequestId' => '123456789A'],
        ],
    ];

    public const TEST_EMPTY_MESSAGE_BODY = [
        'ReceiveMessageResponse' => [
            'ReceiveMessageResult' => [
                'messages' => [
                    [
                        'Attributes' => null,
                        'Body' => '',
                        'MD5OfBody' =>  '609312bab300febd507aaf43938653c5',
                        'MD5OfMessageAttributes' => null,
                        'MessageAttributes' => [],
                        'MessageId' => '123456A',
                        'ReceiptHandle' => 'ABCD',
                    ],
                ],
            ],
            'ResponseMetadata' => ['RequestId' => '123456789A'],
        ],
    ];

    public const TEST_EMPTY_SUCCESS_RESPONSE = [
        'ReceiveMessageResponse' => [
            'ReceiveMessageResult' => [
                'messages' => [],
            ],
            'ResponseMetadata' => ['RequestId' => '123456789A'],
        ],
    ];

     /**
      * Tests if message processing logic is successful
      *
      * @return void
      *
      * @covers ::initialize
      * @covers ::getMessages
      * @covers ::getNs8HttpClient
      * @covers ::getQueueUrl
      * @covers ::parseResponseMessages
      * @covers ::processResultErrors
      * @covers ::setNs8HttpClient
      * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
      * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
      * @covers NS8\ProtectSDK\Config\Manager::getValue
      * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
      * @covers NS8\ProtectSDK\Http\Client::__construct
      * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
      * @covers NS8\ProtectSDK\Http\Client::executeRequest
      * @covers NS8\ProtectSDK\Http\Client::executeWithAuth
      * @covers NS8\ProtectSDK\Http\Client::getAccessToken
      * @covers NS8\ProtectSDK\Http\Client::getAuthUsername
      * @covers NS8\ProtectSDK\Http\Client::getPlatformIdentifier
      * @covers NS8\ProtectSDK\Http\Client::getSessionData
      * @covers NS8\ProtectSDK\Http\Client::post
      * @covers NS8\ProtectSDK\Http\Client::setAccessToken
      * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
      * @covers NS8\ProtectSDK\Http\Client::setSessionData
      * @covers NS8\ProtectSDK\Logging\Client::__construct
      * @covers NS8\ProtectSDK\Logging\Client::addHandler
      * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
      * @covers NS8\ProtectSDK\Logging\Client::info
      * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
      * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
      * @covers NS8\ProtectSDK\Logging\Handlers\Api::__construct
      * @covers NS8\ProtectSDK\Logging\Handlers\Api::getHttpClient
      * @covers NS8\ProtectSDK\Logging\Handlers\Api::initialize
      * @covers NS8\ProtectSDK\Logging\Handlers\Api::write
      * @covers NS8\ProtectSDK\Security\Client::getAuthUser
      * @covers NS8\ProtectSDK\Security\Client::getConfigManager
      * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
      * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
      * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
      */
    public function testSuccessResponse() : void
    {
        $httpClient = $this->buildTestSuccessHttpClient(self::TEST_SUCCESS_RESPONSE);
        QueueClient::initialize($httpClient);
        $messages = QueueClient::getMessages();

        $messageBody =
        self::TEST_SUCCESS_RESPONSE['ReceiveMessageResponse']['ReceiveMessageResult']['messages'][0]['Body'];
        $actualData  = json_decode($messageBody, true);
        $this->assertEquals(
            $actualData['score'],
            $messages[0]['score']
        );

        $messageBody =
        self::TEST_SUCCESS_RESPONSE['ReceiveMessageResponse']['ReceiveMessageResult']['messages'][1]['Body'];
        $actualData  = json_decode($messageBody, true);
        $this->assertEquals(
            $actualData['score'],
            $messages[1]['score']
        );
    }

    /**
     * Tests if message processing logic is successful for empty message arrays
     *
     * @return void
     *
     * @covers ::initialize
     * @covers ::getMessages
     * @covers ::getNs8HttpClient
     * @covers ::getQueueUrl
     * @covers ::parseResponseMessages
     * @covers ::processResultErrors
     * @covers ::setNs8HttpClient
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::executeWithAuth
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::getAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::getPlatformIdentifier
     * @covers NS8\ProtectSDK\Http\Client::getSessionData
     * @covers NS8\ProtectSDK\Http\Client::post
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::__construct
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::getHttpClient
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::initialize
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::write
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     */
    public function testEmptyMessageBodyResponse() : void
    {
        $httpClient = $this->buildTestSuccessHttpClient(self::TEST_EMPTY_MESSAGE_BODY);
        QueueClient::initialize($httpClient);
        $this->expectException(DecodingException::class);

        $messageSet = QueueClient::getMessages();
    }

    /**
     * Tests if message processing logic is successful for empty message arrays
     *
     * @return void
     *
     * @covers ::initialize
     * @covers ::getMessages
     * @covers ::getNs8HttpClient
     * @covers ::getQueueUrl
     * @covers ::parseResponseMessages
     * @covers ::processResultErrors
     * @covers ::setNs8HttpClient
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::executeWithAuth
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::getAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::getPlatformIdentifier
     * @covers NS8\ProtectSDK\Http\Client::getSessionData
     * @covers NS8\ProtectSDK\Http\Client::post
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::__construct
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::getHttpClient
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::initialize
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::write
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     */
    public function testEmptySuccessResponse() : void
    {
        $httpClient = $this->buildTestSuccessHttpClient(self::TEST_EMPTY_SUCCESS_RESPONSE);
        QueueClient::initialize($httpClient);
        $messages = QueueClient::getMessages();

        $this->assertEquals(null, $messages);
    }

    /**
     * Tests if message processing logic throws an error is an error is returned
     *
     * @return void
     *
     * @covers ::initialize
     * @covers ::getMessages
     * @covers ::getNs8HttpClient
     * @covers ::getQueueUrl
     * @covers ::parseResponseMessages
     * @covers ::processResultErrors
     * @covers ::setNs8HttpClient
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::executeWithAuth
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::getAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::getPlatformIdentifier
     * @covers NS8\ProtectSDK\Http\Client::getSessionData
     * @covers NS8\ProtectSDK\Http\Client::post
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::__construct
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::getHttpClient
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::initialize
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::write
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     */
    public function testResponseError() : void
    {
        $httpClient = $this->buildTestExceptionHttpClient();
        QueueClient::initialize($httpClient);
        $this->expectException(ResponseException::class);

        $messageSet = QueueClient::getMessages();
    }

    /**
     * Tests if message deletion works as expected
     *
     * @return void
     *
     * @covers ::deleteMessage
     * @covers ::getNs8HttpClient
     * @covers ::setNs8HttpClient
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::executeWithAuth
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::getAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::getPlatformIdentifier
     * @covers NS8\ProtectSDK\Http\Client::getSessionData
     * @covers NS8\ProtectSDK\Http\Client::post
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::__construct
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::getHttpClient
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::initialize
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::write
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     */
    public function testDeleteMessage() : void
    {
        $isSuccessful = true;
        QueueClient::setNs8HttpClient($this->buildNS8HttpClient(['success' => $isSuccessful]));
        $this->assertEquals($isSuccessful, QueueClient::deleteMessage('123'));
    }

    /**
     * Tests if message processing logic throws an error is an error is returned
     *
     * @return void
     *
     * @covers ::initialize
     * @covers ::getQueueUrl
     * @covers ::getNs8HttpClient
     * @covers ::setNs8HttpClient
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::getPlatformIdentifier
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::__construct
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     */
    public function testGetQueueUrl() : void
    {
        $url = QueueClient::getQueueUrl();
        $this->assertNotEmpty($url);

        // Verify once the URL is set that it is returned
        $url_2 = QueueClient::getQueueUrl();
        $this->assertEquals($url, $url_2);
    }

    /**
     * Tests if message processing logic throws an error if a response is empty
     *
     * @return void
     *
     * @covers ::initialize
     * @covers ::getQueueUrl
     * @covers ::getMessages
     * @covers ::getNs8HttpClient
     * @covers ::parseResponseMessages
     * @covers ::processResultErrors
     * @covers ::setNs8HttpClient
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::executeJsonRequest
     * @covers NS8\ProtectSDK\Http\Client::executeRequest
     * @covers NS8\ProtectSDK\Http\Client::executeWithAuth
     * @covers NS8\ProtectSDK\Http\Client::getAccessToken
     * @covers NS8\ProtectSDK\Http\Client::getAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::getPlatformIdentifier
     * @covers NS8\ProtectSDK\Http\Client::getSessionData
     * @covers NS8\ProtectSDK\Http\Client::post
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
     * @covers NS8\ProtectSDK\Http\Client::setSessionData
     * @covers NS8\ProtectSDK\Logging\Client::__construct
     * @covers NS8\ProtectSDK\Logging\Client::addHandler
     * @covers NS8\ProtectSDK\Logging\Client::getLogLevelIntegerValue
     * @covers NS8\ProtectSDK\Logging\Client::info
     * @covers NS8\ProtectSDK\Logging\Client::setApiHandler
     * @covers NS8\ProtectSDK\Logging\Client::setStreamHandler
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::__construct
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::getHttpClient
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::initialize
     * @covers NS8\ProtectSDK\Logging\Handlers\Api::write
     * @covers NS8\ProtectSDK\Security\Client::getAuthUser
     * @covers NS8\ProtectSDK\Security\Client::getConfigManager
     * @covers NS8\ProtectSDK\Security\Client::getNs8AccessToken
     * @covers NS8\ProtectSDK\Security\Client::validateAuthUser
     * @covers NS8\ProtectSDK\Security\Client::validateNs8AccessToken
     */
    public function testInvalidJsonResponseError() : void
    {
        $httpClient = $this->buildInvalidJsonHttpClient();
        QueueClient::initialize($httpClient);
        $this->expectException(DecodingException::class);

        $messageSet = QueueClient::getMessages();
    }

    /**
     * Sets up Queue Client before a test is ran
     *
     * @return void
     */
    public function setUp() : void
    {
        QueueClient::setNs8HttpClient($this->buildNS8HttpClient(['url' => 'https://example.com']));
    }

    /**
     * Returns a test Laminas HTTP client to utilize when testing successful outputs
     *
     * @param mixed[] $data Array of data that should be present in JSON
     *
     * @return LaminasClient
     */
    protected function buildTestSuccessHttpClient(array $data) : LaminasClient
    {
        $adapter        = new LaminasTestAdapter();
        $testHttpClient = new LaminasClient(self::TEST_URI, ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: application/json' . "\n\n" .
        json_encode($data) .
        "\n";

        $adapter->setResponse($response);

        return $testHttpClient;
    }

    /**
     * Returns a test Laminas HTTP client when expecting an error message
     *
     * @return LaminasClient
     */
    protected function buildTestExceptionHttpClient() : LaminasClient
    {
        $adapter        = new LaminasTestAdapter();
        $testHttpClient = new LaminasClient(self::TEST_URI, ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: application/json' . "\n\n" .
        '{' .
        '   "Error": {' .
        '        "Code": "SignatureDoesNotMatch",' .
        '        "Message": "Signature expired: 20200219T222721Z ' .
        ' is now earlier than 20200219T230626Z (20200319T232126Z - 15 min.)",' .
        '        "Type": "Sender"' .
        '    },' .
        '    "RequestId": "12345"' .
        "}\n";

        $adapter->setResponse($response);

        return $testHttpClient;
    }

    /**
     * Returns a test Laminas HTTP client to test invalid JSON responses
     *
     * @return LaminasClient
     */
    protected function buildInvalidJsonHttpClient() : LaminasClient
    {
        $adapter        = new LaminasTestAdapter();
        $testHttpClient = new LaminasClient(self::TEST_URI, ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: text/html' . "\n\n" .
        'Test Response';

        $adapter->setResponse($response);

        return $testHttpClient;
    }

    /**
     * Returns a test NS8 HTTP Client to use when testing requests
     *
     * @param mixed[] $jsonData JSON data to include in the response
     *
     * @return HttpClient The HTTP Client to use as a stub object
     */
    protected function buildNS8HttpClient(array $jsonData) : HttpClient
    {
        $adapter        = new LaminasTestAdapter();
        $testHttpClient = new LaminasClient('', ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: application/json' . "\n\n" .
        json_encode($jsonData) .
        "\n";

        $adapter->setResponse($response);

        return new HttpClient(null, null, true, $testHttpClient);
    }
}
