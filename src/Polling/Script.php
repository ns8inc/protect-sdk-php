<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Polling;

use Throwable;
use const FILE_APPEND;
use const PHP_EOL;
use function dirname;
use function file_put_contents;
use function getmypid;
use function json_encode;
use function sleep;
use function time;

/**
 * Class to run background polling service
 */
// phpcs:disable
class Script
{
    /**
     * Run the polling script
     *
     * @return void
     */
    public function run() : void
    {
        $currentDirectory = dirname(__FILE__);
        $fileData         = json_encode([
            'process_id' => getmypid(),
            'last_update_time' => time(),
        ]);
        file_put_contents($currentDirectory . '/BACKGROUND_PROCESS_INFO', $fileData);
        while (true) {
            $this->action();
        }
    }

    /**
     * Perform the intended action of the script
     *
     * @return void
     */
    public function action() : void
    {
        try {
            file_put_contents('/tmp/test_output.txt', 'Script is running...' . PHP_EOL, FILE_APPEND);
            sleep(2);
        } catch (Throwable $t) {
            return;
        }
    }
}

(new Script())->run();
// phpcs:enable
