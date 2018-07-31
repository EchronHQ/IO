<?php
declare(strict_types = 1);
require_once 'AbstractTest.php';

class GoogleDriveTest extends AbstractTest
{

    protected function getClient(): \Echron\IO\Client\Base
    {

        return new \Echron\IO\Client\GoogleDrive();
    }

    protected function getRemoteTestFilePath(): string
    {
        return 'test';
    }

    protected function getRemoteTestFileContent(): string
    {
        return '';
    }

//    protected function setUp()
//    {
//        parent::setUp();
//
//        $client = $this->getClient();
//
//        try {
//            if ($client instanceof \Echron\IO\Client\AWSS3) {
//                $client->createBucket($this->bucket);
//            }
//        } catch (\Aws\S3\Exception\S3Exception $ex) {
//            echo 'EX: ' . $ex->getMessage() . PHP_EOL;
//        }
//
//    }
//
//    protected function tearDown()
//    {
//        parent::tearDown();
//        $client = $this->getClient();
//
//        try {
//            if ($client instanceof \Echron\IO\Client\AWSS3) {
//                $client->clearBucket($this->bucket);
//                $client->deleteBucket($this->bucket);
//            }
//        } catch (\Aws\S3\Exception\S3Exception $ex) {
//            echo 'EX: ' . $ex->getMessage() . PHP_EOL;
//        }
//
//    }

}
