<?php
declare(strict_types=1);

class HttpTest extends \PHPUnit\Framework\TestCase
{
    public function testGetFile()
    {
        $destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'googlelogo' . gmdate('U');

        $this->assertFileNotExists($destination);
        $client = new \Echron\IO\Client\Http();
        $client->pull('http://www.google.be/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png', $destination);

        $this->assertFileExists($destination);
    }

    public function testGetFileSize_FileExist()
    {
        $client = new \Echron\IO\Client\Http();
        $size = $client->getRemoteSize('http://www.google.be/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png');

        $this->assertEquals(5087, $size);
    }

    public function testGetFileSize_FileDoesNotExist()
    {
        $client = new \Echron\IO\Client\Http();
        $size = $client->getRemoteSize('http://www.google.be/wuuuuuut/branding/googlelogo/2x/googlelogo_color_120x44dp.png');

        $this->assertEquals(-1, $size);
    }

    public function testGetFileChangeDate_FileExist()
    {
        $client = new \Echron\IO\Client\Http();
        $size = $client->getRemoteChangeDate('http://www.google.be/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png');

        $this->assertEquals(1481158857, $size);
    }

    public function testGetFileChangeDate_FileDoesNotExist()
    {
        $client = new \Echron\IO\Client\Http();
        $size = $client->getRemoteChangeDate('http://www.google.be/wuuuuuut/branding/googlelogo/2x/googlelogo_color_120x44dp.png');

        $this->assertEquals(-1, $size);
    }
}
