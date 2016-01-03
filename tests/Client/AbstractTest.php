<?php
declare(strict_types = 1);

class AbstractTest extends PHPUnit_Framework_TestCase
{
//TODO: create test file when building test
    public function testLocalGetFileSize_FileExist()
    {

        $localTestFile = '../test.txt';

        $this->assertFileExists($localTestFile);

        $client = new \Echron\IO\Client\Http();
        $size = $client->getLocalSize($localTestFile);

        $this->assertEquals(9, $size);
    }

    public function testLocalGetFileSize_FileDoesNotExist()
    {
        $localTestFile = 'nonexisting.txt';

        $this->assertFileNotExists($localTestFile);

        $client = new \Echron\IO\Client\Http();
        $size = $client->getRemoteSize($localTestFile);

        $this->assertEquals(-1, $size);
    }

    public function testLocalGetFileChangeDate_FileExist()
    {
        $localTestFile = '../test.txt';

        $this->assertFileExists($localTestFile);

        $client = new \Echron\IO\Client\Http();
        $size = $client->getLocalChangeDate($localTestFile);

        $this->assertEquals(1488558002, $size);
    }

    public function testLocalGetFileChangeDate_FileDoesNotExist()
    {
        $localTestFile = 'nonexisting.txt';

        $this->assertFileNotExists($localTestFile);

        $client = new \Echron\IO\Client\Http();
        $size = $client->getLocalChangeDate('http://www.google.be/wuuuuuut/branding/googlelogo/2x/googlelogo_color_120x44dp.png');

        $this->assertEquals(-1, $size);
    }
}
