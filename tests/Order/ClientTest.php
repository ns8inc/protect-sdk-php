<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Order;

use AspectMock\Test;
use NS8\ProtectSDK\Order\Client as OrderClient;
use PHPUnit\Framework\TestCase;

/**
 * Order Client test class
 *
 * @coversDefaultClass NS8\ProtectSDK\Order\Client
 */
class ClientTest extends TestCase
{
    /**
     * Cleanup after each test.
     *
     * @return void
     */
    public function tearDown() : void
    {
        Test::clean();
    }

    /**
     * Test getCurrentMerchant().
     *
     * @return void
     *
     * @covers NS8\ProtectSDK\Actions\Client::getEntity
     * @covers NS8\ProtectSDK\Order\Client::getCurrentMerchant
     */
    public function testGetCurrentMerchant() : void
    {
        $mock = Test::double('NS8\ProtectSDK\Actions\Client', [
            'getEntity' => (object) ['httpCode' => 418],
        ]);

        $currentMerchant = OrderClient::getCurrentMerchant();
        $mock->verifyInvokedOnce('getEntity', ['/merchants/current']);
        $this->assertEquals(418, $currentMerchant->httpCode);
    }

    /**
     * Test getOrderByName().
     *
     * @return void
     *
     * @covers NS8\ProtectSDK\Actions\Client::getEntity
     * @covers NS8\ProtectSDK\Order\Client::base64UrlEncode
     * @covers NS8\ProtectSDK\Order\Client::getOrderByName
     */
    public function testGetOrderByName() : void
    {
        $mock = Test::double('NS8\ProtectSDK\Actions\Client', [
            'getEntity' => (object) ['baz' => 'qux'],
        ]);

        // When you encode "foo" in Base64 you get "Zm9v".
        $order = OrderClient::getOrderByName('foo');
        $mock->verifyInvokedOnce('getEntity', ['/orders/order-name/Zm9v']);
        $this->assertEquals('qux', $order->baz);
    }
}
