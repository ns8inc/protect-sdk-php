<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\ClientSdk;

use NS8\ProtectSDK\ClientSdk\Client as ClientSdkClient;
use NS8\ProtectSDK\Config\Manager as ConfigManager;
use PHPUnit\Framework\TestCase;

/**
 * ClientSdk Test Class
 *
 * @coversDefaultClass NS8\ProtectSDK\ClientSdk\Client
 */
class ClientTest extends TestCase
{
    /**
     * Test value returned for fetching the Client SDK URL
     *
     * @return void
     *
     * @covers ::getClientSdkUrl
     * @covers NS8\ProtectSDK\Config\Manager::getEnvValue
     * @covers NS8\ProtectSDK\Config\Manager::getValue
     */
    public function testGetClientSdkUrl() : void
    {
        $trueStatsRoute = ClientSdkClient::getClientSdkUrl();
        $this->assertEquals(
            ConfigManager::getEnvValue('urls.js_sdk'),
            $trueStatsRoute
        );
    }
}
