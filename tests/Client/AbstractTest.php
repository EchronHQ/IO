<?php
declare(strict_types=1);

abstract class AbstractTest extends \PHPUnit\Framework\TestCase
{

    public function testPushFile()
    {
        $client = $this->getClient();

        $localTestFile1 = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent1 = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile1, $localTestFileContent1);

        $fileStat = $client->getLocalFileStat($localTestFile1);
        $remoteLocation = $this->getRemoteTestFilePath();

        $this->assertFalse($client->remoteFileExists($remoteLocation), 'Remote test file "' . $remoteLocation . '" should not exist already');

        $client->pushLazy($localTestFile1, $remoteLocation);
        //TODO: test if file exist on Dropbox storage

        $this->assertExistsOnRemoteAndEquals($client, $remoteLocation, $fileStat, $localTestFileContent1);
    }

    abstract protected function getClient(): \Echron\IO\Client\Base;

    abstract protected function getRemoteTestFilePath(): string;

    protected function assertExistsOnRemoteAndEquals(
        \Echron\IO\Client\Base $client,
        string $remote,
        \Echron\IO\Data\FileStat $fileStat,
        string $content
    ) {
        $local = tempnam(sys_get_temp_dir(), 'io_test');

        $this->assertTrue($client->remoteFileExists($remote), 'Remote file "' . $remote . '" should exist');

        $remoteFileStat = $client->getRemoteFileStat($remote);
        $this->assertTrue($remoteFileStat->getExists(), 'Remote file "' . $remote . '" stats should indicate that it exist');

        $this->assertEquals($fileStat->getBytes(), $remoteFileStat->getBytes(), 'Remote file "' . $remote . '" size should be the same');
        $this->assertEquals($fileStat->getChangeDate(), $remoteFileStat->getChangeDate(), 'Remote file "' . $remote . '" change date should be the same');

        //$this->assertTrue($fileStat->equals($remoteFileStat));

        $client->pull($remote, $local);
        $this->assertFileExists($local, 'Local file "' . $local . '" should exist after pull');
        $fileContent = file_get_contents($local);
        $this->assertEquals($content, $fileContent, 'Local file "' . $local . '" content should be the same after pull');
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
        $client->pushLazy($localTestFile, $remoteLocation);

        //Start test

        $newLocalTestFile = tempnam(sys_get_temp_dir(), 'io_test');

        $this->assertNotEquals($localTestFile, $newLocalTestFile);

        $client->pullLazy($remoteLocation, $newLocalTestFile);

        $this->assertFileExists($newLocalTestFile);

        $newLocalFileStat = $client->getLocalFileStat($newLocalTestFile);

        echo 'Ori: ' . $localFileStat->debug() . ' (' . $localTestFile . ')' . PHP_EOL;
        echo 'New: ' . $newLocalFileStat->debug() . ' (' . $newLocalTestFile . ')' . PHP_EOL;

        echo file_get_contents($newLocalTestFile) . PHP_EOL;

        $this->assertTrue($localFileStat->equals($newLocalFileStat), 'Local file stats should be equal to remote file stats');

        $newLocalFileContent = file_get_contents($newLocalTestFile);
        $this->assertEquals($localTestFileContent, $newLocalFileContent, 'Local file content should be equal with remote file content');
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

        $stat = $client->getRemoteFileStat($remoteLocation);
        $this->assertFalse($stat->getExists(), 'We should get an indication that the file does not exist');

        $this->assertFalse($client->remoteFileExists($remoteLocation), 'The client should indicate that the file does not exist');
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
