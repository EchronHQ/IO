<?php
declare(strict_types=1);

use Echron\IO\Client\SFTP;

require_once 'AbstractTest.php';

class SFTPTest extends AbstractTest
{

    protected function getClient(): \Echron\IO\Client\Base
    {
        $host = '';
        $port = 22;
        $client = new SFTP($host, $port);
        $client->loginWithPassword('', '');

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
