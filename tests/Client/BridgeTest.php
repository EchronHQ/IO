<?php
declare(strict_types = 1);

class BridgeTest extends PHPUnit_Framework_TestCase
{

    public function testPushFromMasterToSlave()
    {

        $master = new \Echron\IO\Client\Memory();
        $slave = new \Echron\IO\Client\Memory();
        $bridge = new \Echron\IO\Client\Bridge($master, $slave);

        $localTestFile1 = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent1 = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile1, $localTestFileContent1);

        $fileStat = $master->getLocalFileStat($localTestFile1);

        $remoteTestLocation = 'master/test';
        $master->push($localTestFile1, $remoteTestLocation);

        $client2remoteTestLocation = 'slave/test';

        $bridge->push($remoteTestLocation, $client2remoteTestLocation);

        $this->assertExistsOnRemoteAndEquals($slave, $client2remoteTestLocation, $fileStat, $localTestFileContent1);

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

    public function testPullFromSlaveToMaster()
    {

        $master = new \Echron\IO\Client\Memory();
        $slave = new \Echron\IO\Client\Memory();
        $bridge = new \Echron\IO\Client\Bridge($master, $slave);

        $localTestFile1 = tempnam(sys_get_temp_dir(), 'io_test');
        $localTestFileContent1 = 'ThisIsATestFile' . uniqid();

        file_put_contents($localTestFile1, $localTestFileContent1);

        $fileStat = $master->getLocalFileStat($localTestFile1);

        $remoteTestLocation = 'master/test';
        $slave->push($localTestFile1, $remoteTestLocation);

        $client2remoteTestLocation = 'slave/test';

        $bridge->pull($remoteTestLocation, $client2remoteTestLocation);

        $this->assertExistsOnRemoteAndEquals($master, $client2remoteTestLocation, $fileStat, $localTestFileContent1);

    }

}
