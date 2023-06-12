<?php

declare(strict_types=1);

use Cache\Adapter\PHPArray\ArrayCachePool;

require_once 'AbstractTest.php';

class CacheTest extends AbstractTest
{
    protected function getClient(): \Echron\IO\Client\Base
    {
        $pool = new ArrayCachePool();

        $cacheClient = new \Echron\IO\Client\Cache($pool);

        return $cacheClient;
    }

    protected function getRemoteTestFilePath(): string
    {
        return '/testfile_' . uniqid() . '.txt';
    }

    protected function getRemoteTestFileContent(): string
    {
        // TODO: Implement getRemoteTestFileContent() method.
    }
}
