<?php
declare(strict_types=1);

class GeneralTest extends \PHPUnit\Framework\TestCase
{
    private $existingTestFile;
    private $nonExistingTestFile = 'notexistingtest.txt';
    private $changeDate = 0;

    public function testLocalGetFileSize_FileExist()
    {
        $this->assertFileExists($this->existingTestFile);

        $client = new \Echron\IO\Client\Http();
        $size = $client->getLocalSize($this->existingTestFile);

        $this->assertEquals(15, $size);
    }

//TODO: create test file when building test

    public function testLocalGetFileSize_FileDoesNotExist()
    {
        $this->assertFileNotExists($this->nonExistingTestFile);

        $client = new \Echron\IO\Client\Http();
        $size = $client->getRemoteSize($this->nonExistingTestFile);

        $this->assertEquals(-1, $size);
    }

    public function testLocalGetFileChangeDate_FileExist()
    {
        $this->assertFileExists($this->existingTestFile);

        $client = new \Echron\IO\Client\Http();
        $size = $client->getLocalChangeDate($this->existingTestFile);

        $this->assertEquals($this->changeDate, $size);
    }

    public function testLocalGetFileChangeDate_FileDoesNotExist()
    {
        $this->assertFileNotExists($this->nonExistingTestFile);

        $client = new \Echron\IO\Client\Http();
        $size = $client->getLocalChangeDate($this->nonExistingTestFile);

        $this->assertEquals(-1, $size);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->removeTestFiles();

        $this->changeDate = date('U');

        $this->existingTestFile = tempnam(sys_get_temp_dir(), 'io_test');
        file_put_contents($this->existingTestFile, 'ThisIsATestFile');
    }

    private function removeTestFiles()
    {
        if ($this->existingTestFile !== null && file_exists($this->existingTestFile)) {
            unlink($this->existingTestFile);
        }
        if ($this->nonExistingTestFile !== null && file_exists($this->nonExistingTestFile)) {
            unlink($this->nonExistingTestFile);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->removeTestFiles();
    }
}
