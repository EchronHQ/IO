<?php
declare(strict_types = 1);

class HttpTest extends PHPUnit_Framework_TestCase
{
    public function testGetFile()
    {
        $destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'googlelogo' . gmdate('U');

        $this->assertFileNotExists($destination);
        $client = new \Echron\IO\Client\Http();
        $client->pull('http://www.google.be/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png', $destination);

        $this->assertFileExists($destination);
    }
}
