<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Polling;

use AspectMock\Test;
use NS8\ProtectSDK\Polling\Client as PollingClient;
use PHPUnit\Framework\TestCase;
use function dirname;
use function file_exists;
use function sleep;
use function unlink;

/**
 * Polling Client test class
 *
 * @coversDefaultClass NS8\ProtectSDK\Polling\Client
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
     * Test to ensure service can be started successfully
     *
     * @return void
     *
     * @covers ::checkProcessRuntime
     * @covers ::isServiceRunning
     * @covers ::killService
     * @covers ::getPHPBinaryPath
     * @covers ::getProcessDetails
     * @covers ::getProcessId
     * @covers ::getProcessIdFilePath
     * @covers ::getServiceCommand
     * @covers ::removeProcessIdFile
     * @covers ::startService
     */
    public function testStartService() : void
    {
        PollingClient::startService();
        $this->waitForProcessFile();
        $this->assertEquals(true, PollingClient::isServiceRunning());
        PollingClient::killService();
        $this->assertEquals(false, PollingClient::isServiceRunning());
    }

    /**
     * Test to ensure accuracy of isServiceRunning() method
     *
     * @return void
     *
     * @covers ::checkProcessRuntime
     * @covers ::isServiceRunning
     * @covers ::killService
     * @covers ::getPHPBinaryPath
     * @covers ::getProcessDetails
     * @covers ::getProcessId
     * @covers ::getProcessIdFilePath
     * @covers ::getServiceCommand
     * @covers ::removeProcessIdFile
     * @covers ::startService
     */
    public function testIsServiceRunning() : void
    {
        $this->assertEquals(null, PollingClient::getProcessId());
        $this->assertEquals(false, PollingClient::isServiceRunning());
        PollingClient::startService();
        $this->waitForProcessFile();
        $this->assertEquals(true, PollingClient::isServiceRunning());

        PollingClient::startService();
        $this->waitForProcessFile();

        PollingClient::killService();
        $this->assertEquals(false, PollingClient::isServiceRunning());
    }

    /**
     * Test to ensure process restarts happen as needed
     *
     * @return void
     *
     * @covers ::checkProcessRuntime
     * @covers ::isServiceRunning
     * @covers ::killService
     * @covers ::getPHPBinaryPath
     * @covers ::getProcessDetails
     * @covers ::getProcessId
     * @covers ::getProcessIdFilePath
     * @covers ::getServiceCommand
     * @covers ::removeProcessIdFile
     * @covers ::startService
     */
    public function testProcessRestart() : void
    {
        $originalMaxRunTimeDuration = PollingClient::getMaxRunTime();
        PollingClient::setMaxRunTime('0 seconds');
        $this->assertEquals(false, PollingClient::isServiceRunning());
        PollingClient::startService();
        $this->waitForProcessFile();
        $processId = PollingClient::getProcessId();
        PollingClient::startService();
        $this->waitForProcessFile();
        $newProcessId = PollingClient::getProcessId();
        $this->assertNotEquals($processId, $newProcessId);
        PollingClient::killService();
        PollingClient::setMaxRunTime($originalMaxRunTimeDuration);
    }

    /**
     * Test to ensure killing the service happens as intended
     *
     * @return void
     *
     * @covers ::isServiceRunning
     * @covers ::killService
     * @covers ::getPHPBinaryPath
     * @covers ::getProcessDetails
     * @covers ::getProcessId
     * @covers ::getProcessIdFilePath
     * @covers ::getServiceCommand
     * @covers ::removeProcessIdFile
     * @covers ::startService
     */
    public function testServiceKill() : void
    {
        PollingClient::startService();
        $this->waitForProcessFile();
        PollingClient::killService();
        $this->assertEquals(false, PollingClient::isServiceRunning());

        // Killing the service multiple times should not trigger an error
        PollingClient::killService();
        $this->assertEquals(false, PollingClient::isServiceRunning());
    }

    /**
     * Test to ensure script loads as intended
     *
     * @return void
     *
     * @covers ::isServiceRunning
     * @covers ::getProcessIdFilePath
     * @covers NS8\ProtectSDK\Polling\Script::action
     * @covers NS8\ProtectSDK\Polling\Script::run
     */
    public function testScriptLoading() : void
    {
        $GLOBALS['ns8_unit_testing_env'] = true;
        require dirname(__FILE__) . '/../../src/Polling/Script.php';

        $this->waitForProcessFile();
        $this->assertEquals(true, PollingClient::isServiceRunning());
        unlink(PollingClient::getProcessIdFilePath());
        unset($GLOBALS['ns8_unit_testing_env']);
    }

    /**
     * Wait for process file to show up to avoid race conditions during tests
     *
     * @return void
     */
    protected function waitForProcessFile() : void
    {
        $checkCount    = 0;
        $doesFileExist = file_exists(PollingClient::getProcessIdFilePath());
        while (! $doesFileExist && $checkCount < 3) {
            sleep(1);
            $doesFileExist = file_exists(PollingClient::getProcessIdFilePath());
            $checkCount++;
        }
    }
}
