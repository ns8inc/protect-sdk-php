<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Templates;

use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Templates\Client as TemplatesClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function array_merge;
use function http_build_query;
use function sprintf;

/**
 * Templates Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\Templates\Client
 */
class ClientTest extends TestCase
{
    /**
     * The Templates Client instance we use for testing.
     *
     * @var TemplatesClient
     */
    protected $templatesClient;

    /**
     * Runs before every test.
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->templatesClient = new TemplatesClient();
    }

    /**
     * Test getting/setting the HTTP client.
     *
     * @return void
     *
     * @covers NS8\ProtectSDK\Config\Manager::doesValueExist
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     * @covers NS8\ProtectSDK\Config\ManagerStructure::initConfiguration
     * @covers NS8\ProtectSDK\Http\Client::__construct
     * @covers NS8\ProtectSDK\Http\Client::setAccessToken
     * @covers NS8\ProtectSDK\Http\Client::setAuthUsername
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
     * @covers NS8\ProtectSDK\Templates\Client::getHttpClient
     * @covers NS8\ProtectSDK\Templates\Client::setHttpClient
     */
    public function testGetAndSetHttpClient() : void
    {
        $httpClient = new HttpClient();
        $this->templatesClient->setHttpClient($httpClient);
        $this->assertSame($httpClient, $this->templatesClient->getHttpClient());
    }

    /**
     * Test getting an invalid view.
     *
     * @return void
     *
     * @covers NS8\ProtectSDK\Templates\Client::get
     */
    public function testGetInvalidView() : void
    {
        $exceptionCaught = false;

        try {
            $this->templatesClient->get('this-is-an-invalid-view', '123', 'abc', 'foo', 'http://example.org');
        } catch (RuntimeException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Invalid view "this-is-an-invalid-view" specified', $e->getMessage());
        }

        $this->assertTrue($exceptionCaught);
    }

    /**
     * Test GETing a valid view.
     *
     * @return void
     *
     * @covers NS8\ProtectSDK\Templates\Client::get
     * @covers NS8\ProtectSDK\Templates\Client::getHttpClient
     * @covers NS8\ProtectSDK\Templates\Client::setHttpClient
     */
    public function testGetValidView() : void
    {
        $params = [
            'orderId' => '123',
            'returnUri' => 'http://example.org',
            'token' => 'abc',
            'verificationId' => 'foo',
            'view' => 'orders-validate',
        ];

        $template       = (object) ['html' => '<html><body>Foo</body></html>'];
        $httpClientMock = $this->createMock(HttpClient::class);
        $this->templatesClient->setHttpClient($httpClientMock);

        $httpClientMock->expects($this->once())
            ->method('get')
            ->with(sprintf('/merchant/template?%s', http_build_query($params)))
            ->willReturn($template);

        $this->assertEquals($template, $this->templatesClient->get(
            $params['view'],
            $params['orderId'],
            $params['token'],
            $params['verificationId'],
            $params['returnUri']
        ));
    }

    /**
     * Test POSTing a valid view.
     *
     * @return void
     *
     * @covers NS8\ProtectSDK\Templates\Client::get
     * @covers NS8\ProtectSDK\Templates\Client::getHttpClient
     * @covers NS8\ProtectSDK\Templates\Client::setHttpClient
     */
    public function testPostValidView() : void
    {
        $params = [
            'orderId' => '123',
            'returnUri' => 'http://example.org/',
            'token' => 'abc',
            'verificationId' => 'foo',
            'view' => 'orders-validate',
        ];

        $postParams = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $template       = (object) ['location' => 'http://example.org/'];
        $httpClientMock = $this->createMock(HttpClient::class);
        $this->templatesClient->setHttpClient($httpClientMock);

        $httpClientMock->expects($this->once())
            ->method('post')
            ->with('/merchant/template', array_merge($params, $postParams))
            ->willReturn($template);

        $this->assertEquals($template, $this->templatesClient->get(
            $params['view'],
            $params['orderId'],
            $params['token'],
            $params['verificationId'],
            $params['returnUri'],
            $postParams
        ));
    }
}
