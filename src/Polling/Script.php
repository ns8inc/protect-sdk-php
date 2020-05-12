<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Polling;

use const FILE_APPEND;
use const PHP_EOL;
use function dirname;
use function file_put_contents;
use function getmypid;
use function json_encode;
use function sleep;
use function time;

/**
 * Class to run background polling service.
 * Disable linting in this file to permit class declaration and execution for running the script.
 */
// phpcs:disable
class Script
{
    /**
     * Globals key to look for when running specific unit tests to avoid infinite loop trigger
     */
    const UNIT_TEST_PRESENT_KEY = 'ns8_unit_testing_env';
    /**
     * Run the polling script
     *
     * @return void
     */
    public function run() : void
    {
        // Provides an outlet for unit testing in a fashion that still enables outside process run-time
        $shouldRunContinuous = !(isset($GLOBALS[self::UNIT_TEST_PRESENT_KEY]) && $GLOBALS[self::UNIT_TEST_PRESENT_KEY]);
        $currentDirectory = dirname(__FILE__);
        $fileData         = json_encode([
            'process_id' => getmypid(),
            'last_update_time' => time(),
        ]);
        file_put_contents($currentDirectory . '/BACKGROUND_PROCESS_INFO', $fileData);
        do {
            $this->action();
        } while ($shouldRunContinuous);
    }

    /**
     * Perform the intended action of the script
     *
     * @return void
     */
    public function action() : void
    {
        file_put_contents('/tmp/test_output.txt', 'Script is running...' . PHP_EOL, FILE_APPEND);
        sleep(2);
    }
}
// @codeCoverageIgnoreStart
(new Script())->run();
// @codeCoverageIgnoreEnd
// phpcs:enable
