<?php
declare(strict_types = 1);
require_once 'AbstractTest.php';

class DropboxTest extends AbstractTest
{
    private $appKey = '85iwawrxvz2cbly';
    private $appSecret = 'vxcaomv7ap599qi';
    private $accessToken = 'yTeTDvUWwucAAAAAAACtITnaKlaXvfiyfZn4NwOmITZrhcYhAHFNuQ5ucag3jTII';



    protected function getClient(): \Echron\IO\Client\Base
    {
        return new \Echron\IO\Client\Dropbox($this->appKey, $this->appSecret, $this->accessToken);
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
