<?php
declare(strict_types=1);
require_once 'AbstractTest.php';

class CacheTest extends AbstractTest
{
    protected function getClient(): \Echron\IO\Client\Base
    {
        $manager = new \MongoDB\Driver\Manager('mongodb://178.117.199.50');

        $collection = new \MongoDB\Collection($manager, 'attlaz', 'test');

//$collection = \Cache\Adapter\MongoDB\MongoDBCachePool::createCollection($manager, '178.117.199.50:27017', 'attlaz.test');

//var_dump($collection);
//die();
        $pool = new \Cache\Adapter\MongoDB\MongoDBCachePool($collection);

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