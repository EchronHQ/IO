<?php
declare(strict_types = 1);

class MemoryTest extends PHPUnit_Framework_TestCase
{
    public function testPush()
    {
        $client = new \Echron\IO\Client\Memory();

        $localTestFile = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile, $localTestFileContent);

        $remoteTestLocation = 'root/test';

        $fileStat = $client->getLocalFileStat($localTestFile);

        $this->assertFileExists($localTestFile);
        $this->assertFalse($client->remoteFileExists($remoteTestLocation));

        $client->push($localTestFile, $remoteTestLocation);

        $this->assertExistsOnRemoteAndEquals($client, $remoteTestLocation, $fileStat, $localTestFileContent);
        $this->assertTrue($client->remoteFileExists($remoteTestLocation));

    }

    private function assertExistsOnRemoteAndEquals(\Echron\IO\Client\Base $client, string $remote, \Echron\IO\Data\FileStat $fileStat, string $content)
    {
        $local = tempnam(sys_get_temp_dir(), 'io_test');

        //
        $remoteFileStat = $client->getRemoteFileStat($remote);
        $this->assertTrue($fileStat->equals($remoteFileStat));

        $client->pull($remote, $local);
        $this->assertFileExists($local);
        $fileContent = file_get_contents($local);
        $this->assertEquals($content, $fileContent);

    }

    public function testPull()
    {
        $client = new \Echron\IO\Client\Memory();

        $localTestFile = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile, $localTestFileContent);

        $remoteTestLocation = 'root/test';

        $localFileStat = $client->getLocalFileStat($localTestFile);
        $client->push($localTestFile, $remoteTestLocation);

        //Start test

        $newLocalTestFile = tempnam(sys_get_temp_dir(), 'io_test');

        $this->assertNotEquals($localTestFile, $newLocalTestFile);

        $client->pull($remoteTestLocation, $newLocalTestFile);

        $this->assertFileExists($newLocalTestFile);
        $newLocalFileStat = $client->getLocalFileStat($newLocalTestFile);

        $this->assertTrue($localFileStat->equals($newLocalFileStat));

        $newLocalFileContent = file_get_contents($newLocalTestFile);
        $this->assertEquals($localTestFileContent, $newLocalFileContent);

    }

}
