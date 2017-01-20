<?php
declare(strict_types = 1);

abstract class AbstractTest extends PHPUnit_Framework_TestCase
{

    public function testPushFile()
    {
        $client = $this->getClient();

        $localTestFile1 = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent1 = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile1, $localTestFileContent1);

        $fileStat = $client->getLocalFileStat($localTestFile1);
        $remoteLocation = $this->getRemoteTestFilePath();

        $this->assertFalse($client->remoteFileExists($remoteLocation));

        $client->push($localTestFile1, $remoteLocation);
        //TODO: test if file exist on Dropbox storage

        $this->assertExistsOnRemoteAndEquals($client, $remoteLocation, $fileStat, $localTestFileContent1);

    }

    abstract protected function getClient(): \Echron\IO\Client\Base;

    abstract protected function getRemoteTestFilePath(): string;

    protected function assertExistsOnRemoteAndEquals(\Echron\IO\Client\Base $client, string $remote, \Echron\IO\Data\FileStat $fileStat, string $content)
    {
        $local = tempnam(sys_get_temp_dir(), 'io_test');

        $this->assertTrue($client->remoteFileExists($remote));

        $remoteFileStat = $client->getRemoteFileStat($remote);

        $this->assertEquals($fileStat->getBytes(), $remoteFileStat->getBytes());
        $this->assertEquals($fileStat->getChangeDate(), $remoteFileStat->getChangeDate());

        //$this->assertTrue($fileStat->equals($remoteFileStat));

        $client->pull($remote, $local);
        $this->assertFileExists($local);
        $fileContent = file_get_contents($local);
        $this->assertEquals($content, $fileContent);

    }

//    public function testPullFile()
//    {
//        $client = $this->getClient();
//
//        $localTestFile1 = tempnam(sys_get_temp_dir(), 'io_test');
//        $localTestFileContent1 = 'ThisIsATestFile' . uniqid();
//
//        file_put_contents($localTestFile1, $localTestFileContent1);
//
//        $remoteLocation = $this->getRemoteTestFilePath();
//
//        $client->push($localTestFile1, $remoteLocation);
//
//        $newLocalLocation = tempnam(sys_get_temp_dir(), 'io_test');
//
//        $client->pull($remoteLocation, $newLocalLocation);
//
//    }

    public function testPull()
    {
        $client = $this->getClient();

        $localTestFile = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile, $localTestFileContent);

        $remoteLocation = $this->getRemoteTestFilePath();

        $localFileStat = $client->getLocalFileStat($localTestFile);
        $client->push($localTestFile, $remoteLocation);

        //Start test

        $newLocalTestFile = tempnam(sys_get_temp_dir(), 'io_test');

        $this->assertNotEquals($localTestFile, $newLocalTestFile);

        $client->pull($remoteLocation, $newLocalTestFile);

        $this->assertFileExists($newLocalTestFile);
        $newLocalFileStat = $client->getLocalFileStat($newLocalTestFile);

        $this->assertTrue($localFileStat->equals($newLocalFileStat));

        $newLocalFileContent = file_get_contents($newLocalTestFile);
        $this->assertEquals($localTestFileContent, $newLocalFileContent);

    }

    public function testFileExist_Exists()
    {
        $client = $this->getClient();

        $localTestFile1 = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent1 = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile1, $localTestFileContent1);

        $remoteLocation = $this->getRemoteTestFilePath();

        $client->push($localTestFile1, $remoteLocation);

        $this->assertTrue($client->remoteFileExists($remoteLocation));

    }

    public function testFileExist_DoesNotExists()
    {
        $client = $this->getClient();

        $remoteLocation = $this->getRemoteTestFilePath();
        $client->delete($remoteLocation);
        $this->assertFalse($client->remoteFileExists($remoteLocation));

    }

    public function testDelete_Existing()
    {
        $client = $this->getClient();
        $localTestFile = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile, $localTestFileContent);

        $remoteTestLocation = $this->getRemoteTestFilePath();

        $client->push($localTestFile, $remoteTestLocation);

        $this->assertTrue($client->remoteFileExists($remoteTestLocation));

        $client->delete($remoteTestLocation);

        $this->assertFalse($client->remoteFileExists($remoteTestLocation));
    }

    abstract protected function getRemoteTestFileContent(): string;
}
