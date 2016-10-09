<?php
declare(strict_types = 1);

class DropboxTest extends PHPUnit_Framework_TestCase
{
    private $appKey = '85iwawrxvz2cbly';
    private $appSecret = 'vxcaomv7ap599qi';
    private $accessToken = 'yTeTDvUWwucAAAAAAACtITnaKlaXvfiyfZn4NwOmITZrhcYhAHFNuQ5ucag3jTII';

    public function testPushFile()
    {
        $client = new \Echron\IO\Client\Dropbox($this->appKey, $this->appSecret, $this->accessToken);

        $localTestFile1 = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent1 = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile1, $localTestFileContent1);

        $fileStat = $client->getLocalFileStat($localTestFile1);
        $remoteLocation = '/testfile_' . uniqid() . '.txt';
        $client->push($localTestFile1, $remoteLocation);
        //TODO: test if file exist on Dropbox storage

        $this->assertExistsOnRemoteAndEquals($client, $remoteLocation, $fileStat, $localTestFileContent1);

    }

    private function assertExistsOnRemoteAndEquals(\Echron\IO\Client\Base $client, string $remote, \Echron\IO\Data\FileStat $fileStat, string $content)
    {
        $local = tempnam(sys_get_temp_dir(), 'io_test');

        $remoteFileStat = $client->getRemoteFileStat($remote);

        $this->assertEquals($fileStat->getBytes(), $remoteFileStat->getBytes());
        $this->assertEquals($fileStat->getChangeDate(), $remoteFileStat->getChangeDate());

        //$this->assertTrue($fileStat->equals($remoteFileStat));

        $client->pull($remote, $local);
        $this->assertFileExists($local);
        $fileContent = file_get_contents($local);
        $this->assertEquals($content, $fileContent);

    }

    public function testPullFile()
    {
        $client = new \Echron\IO\Client\Dropbox($this->appKey, $this->appSecret, $this->accessToken);

        $localTestFile1 = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent1 = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile1, $localTestFileContent1);

        $remoteLocation = '/testfile_' . uniqid() . '.txt';

        $client->push($localTestFile1, $remoteLocation);

        $newLocalLocation = tempnam(sys_get_temp_dir(), 'io_test');

        $client->pull($remoteLocation, $newLocalLocation);

    }

    public function testFileExist_Exists()
    {
        $client = new \Echron\IO\Client\Dropbox($this->appKey, $this->appSecret, $this->accessToken);

        $localTestFile1 = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent1 = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile1, $localTestFileContent1);

        $remoteLocation = '/testfile_' . uniqid() . '.txt';

        $client->push($localTestFile1, $remoteLocation);

        $this->assertTrue($client->remoteFileExists($remoteLocation));

    }

    public function testFileExist_DoesNotExists()
    {
        $client = new \Echron\IO\Client\Dropbox($this->appKey, $this->appSecret, $this->accessToken);

        $remoteLocation = '/testfile_' . uniqid() . '.txt';

        $this->assertFalse($client->remoteFileExists($remoteLocation));

    }

    public function testDelete_Existing()
    {
        $client = new \Echron\IO\Client\Dropbox($this->appKey, $this->appSecret, $this->accessToken);

        $localTestFile = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile, $localTestFileContent);

        $remoteTestLocation = '/testfile_' . uniqid() . '.txt';

        $client->push($localTestFile, $remoteTestLocation);

        $this->assertTrue($client->remoteFileExists($remoteTestLocation));

        $client->delete($remoteTestLocation);

        $this->assertFalse($client->remoteFileExists($remoteTestLocation));
    }

}
