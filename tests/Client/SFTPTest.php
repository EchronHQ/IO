<?php

declare(strict_types=1);

use Echron\IO\Client\Base;
use Echron\IO\Client\SFTP;

require_once 'AbstractTest.php';

class SFTPTest extends AbstractTest
{
    protected function getClient(): Base
    {
        $host = 'sftp-test';
        $port = 22;
        $client = new SFTP($host, $port);
        $client->loginWithPassword('demo', 'demo');

        return $client;
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
