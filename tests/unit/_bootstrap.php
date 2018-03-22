<?php
include __DIR__.'/../../vendor/autoload.php';

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'appDir' => __DIR__ . '/../../',
    'cacheDir'  => __DIR__ . '/../_support/_cache',
    'debug'    => true,
    'includePaths' => [
        __DIR__ . '/../../src',
        __DIR__ . '/../_support/Fixture',
    ],
]);