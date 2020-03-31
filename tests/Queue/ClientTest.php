<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Queue;

use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Queue\Client as QueueClient;
use NS8\ProtectSDK\Queue\Exceptions\Decoding as DecodingException;
use NS8\ProtectSDK\Queue\Exceptions\Response as ResponseException;
use PHPUnit\Framework\TestCase;
use Zend\Http\Client as ZendClient;
use Zend\Http\Client\Adapter\Test as ZendTestAdapter;
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
      * @covers ::getQueueUrl
      * @covers ::getMessages
      * @covers ::parseResponseMessages
      * @covers ::processResultErrors
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
     * @covers ::getQueueUrl
     * @covers ::getMessages
     * @covers ::parseResponseMessages
     * @covers ::processResultErrors
     */
    public function testEmptymessageBodyResponse() : void
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
     * @covers ::getQueueUrl
     * @covers ::getMessages
     * @covers ::parseResponseMessages
     * @covers ::processResultErrors
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
     * @covers ::getQueueUrl
     * @covers ::getMessages
     * @covers ::parseResponseMessages
     * @covers ::processResultErrors
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
     */
    public function testDeleteMessage() : void
    {
        QueueClient::setNs8HttpClient($this->buildNS8DeleteHttpClient());
        $this->assertEquals(true, QueueClient::deleteMessage('123'));
    }

    /**
     * Tests if message processing logic throws an error is an error is returned
     *
     * @return void
     *
     * @covers ::initialize
     * @covers ::getQueueUrl
     */
    public function testGetQueueUrl() : void
    {
        $url = QueueClient::getQueueUrl();
        $this->assertEmpty($url);

        QueueClient::initialize(null, 'https://google.com');
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
     * @covers ::parseResponseMessages
     * @covers ::processResultErrors
     */
    public function testInvalidJsonResponseError() : void
    {
        $httpClient = $this->buildInvalidJsonHttpClient();
        QueueClient::initialize($httpClient);
        $this->expectException(DecodingException::class);

        $messageSet = QueueClient::getMessages();
    }

    /**
     * Returns a test Zend HTTP client to utilize when testing successful outputs
     *
     * @param mixed[] $data Array of data that should be present in JSON
     *
     * @return ZendClient
     */
    protected function buildTestSuccessHttpClient(array $data) : ZendClient
    {
        $adapter        = new ZendTestAdapter();
        $testHttpClient = new ZendClient(self::TEST_URI, ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: application/json' . "\n\n" .
        json_encode($data) .
        "\n";

        $adapter->setResponse($response);

        return $testHttpClient;
    }

    /**
     * Returns a test Zend HTTP client when expecting an error message
     *
     * @return ZendClient
     */
    protected function buildTestExceptionHttpClient() : ZendClient
    {
        $adapter        = new ZendTestAdapter();
        $testHttpClient = new ZendClient(self::TEST_URI, ['adapter' => $adapter]);

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
     * Returns a test Zend HTTP client to test invalid JSON responses
     *
     * @return ZendClient
     */
    protected function buildInvalidJsonHttpClient() : ZendClient
    {
        $adapter        = new ZendTestAdapter();
        $testHttpClient = new ZendClient(self::TEST_URI, ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: text/html' . "\n\n" .
        'Test Response';

        $adapter->setResponse($response);

        return $testHttpClient;
    }

    /**
     * Returns a test NS8 HTTP Client to use when testing delete requests
     *
     * @return HttpClient The HTTP Client to use as a stub object
     */
    protected function buildNS8DeleteHttpClient() : HttpClient
    {
        $adapter        = new ZendTestAdapter();
        $testHttpClient = new ZendClient('', ['adapter' => $adapter]);

        $response =  'HTTP/1.1 200 OK' . "\n" .
        'Content-type: application/json' . "\n\n" .
        '{' .
        '   "success": ' . true .
        "}\n";

        $adapter->setResponse($response);

        return new HttpClient(null, null, true, $testHttpClient);
    }
}
