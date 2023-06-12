<?php

declare(strict_types=1);

use Echron\IO\Client\Base;
use Echron\IO\Client\SFTP;

require_once 'AbstractTest.php';

class FTPTest extends AbstractTest
{
    protected function getClient(): Base
    {
        $host = 'sftp-test';
        $port = 22;
        $client = new SFTP($host, $port);
        $client->loginWithPassword('demo', 'demo');


        $host = 'ftp.asuivrebe.webhosting.be';
        $port = 21;
        $user = 'asuivrebe@asuivrebe';
        $password = 'R4ZR7g3YjdkzJqfr';
        $passive = true;
        $client = new \Echron\IO\Client\FtpClient($host, $user, $password, $port, $passive);

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
