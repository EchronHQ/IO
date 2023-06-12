<?php

declare(strict_types=1);
require_once 'AbstractTest.php';

class MemoryTest extends AbstractTest
{
    //
    //    public function testPull()
    //    {
    //        $client = new \Echron\IO\Client\Memory();
    //
    //        $localTestFile = tempnam(sys_get_temp_dir(), 'io_test');
    //        $localTestFileContent = 'ThisIsATestFile' . uniqid();
    //
    //        file_put_contents($localTestFile, $localTestFileContent);
    //
    //        $remoteTestLocation = 'root/test';
    //
    //        $localFileStat = $client->getLocalFileStat($localTestFile);
    //        $client->push($localTestFile, $remoteTestLocation);
    //
    //        //Start test
    //
    //        $newLocalTestFile = tempnam(sys_get_temp_dir(), 'io_test');
    //
    //        $this->assertNotEquals($localTestFile, $newLocalTestFile);
    //
    //        $client->pull($remoteTestLocation, $newLocalTestFile);
    //
    //        $this->assertFileExists($newLocalTestFile);
    //        $newLocalFileStat = $client->getLocalFileStat($newLocalTestFile);
    //
    //        $this->assertTrue($localFileStat->equals($newLocalFileStat));
    //
    //        $newLocalFileContent = file_get_contents($newLocalTestFile);
    //        $this->assertEquals($localTestFileContent, $newLocalFileContent);
    //
    //    }
    //
    //    public function testDelete_Existing()
    //    {
    //        $client = new \Echron\IO\Client\Memory();
    //
    //        $localTestFile = tempnam(sys_get_temp_dir(), 'io_test');
    //        $localTestFileContent = 'ThisIsATestFile' . uniqid();
    //
    //        file_put_contents($localTestFile, $localTestFileContent);
    //
    //        $remoteTestLocation = 'root/test';
    //
    //        $client->push($localTestFile, $remoteTestLocation);
    //
    //        $this->assertTrue($client->remoteFileExists($remoteTestLocation));
    //
    //        $client->delete($remoteTestLocation);
    //
    //        $this->assertFalse($client->remoteFileExists($remoteTestLocation));
    //    }

    protected function getClient(): \Echron\IO\Client\Base
    {
        return new \Echron\IO\Client\Memory();
    }

    protected function getRemoteTestFilePath(): string
    {
        return 'root/test';
    }

    protected function getRemoteTestFileContent(): string
    {
        // TODO: Implement getRemoteTestFileContent() method.
    }
}
