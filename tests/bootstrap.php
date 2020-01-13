<?php

declare(strict_types=1);

use AspectMock\Kernel;

Kernel::getInstance()->init([
    'cacheDir' => '/tmp/protect-php-sdk',
    'debug' => true,
    'includePaths' => [
        dirname(__DIR__) . '/src',
    ],
]);
