<?php
declare(strict_types = 1);
require_once 'AbstractTest.php';

class AWSS3Test extends AbstractTest
{
    protected function getClient(): \Echron\IO\Client\Base
    {
        return new \Echron\IO\Client\AWSS3();
    }

    protected function getRemoteTestFilePath(): string
    {
        return 'test';
    }

    protected function getRemoteTestFileContent(): string
    {
        return '';
    }

}
