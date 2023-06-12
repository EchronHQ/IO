<?php

declare(strict_types=1);
require_once 'AbstractTest.php';

class AWSS3Test extends AbstractTest
{
    private $bucket = 'io.test2';

    protected function getClient(): \Echron\IO\Client\Base
    {
        $credentials = [
            'key'    => 'AKIAJLWY2ODOND3HSPSQ',
            'secret' => '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',
        ];

        return new \Echron\IO\Client\AWSS3($this->bucket, $credentials);
    }

    protected function getRemoteTestFilePath(): string
    {
        return 'test';
    }

    protected function getRemoteTestFileContent(): string
    {
        return '';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $client = $this->getClient();

        try {
            if ($client instanceof \Echron\IO\Client\AWSS3) {
                $client->createBucket($this->bucket);
            }
        } catch (\Aws\S3\Exception\S3Exception $ex) {
            $this->markTestSkipped(
                'Unable to set up AWSS3 Bucket'
            );
        }

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $client = $this->getClient();

        try {
            if ($client instanceof \Echron\IO\Client\AWSS3) {
                $client->clearBucket($this->bucket);
                $client->deleteBucket($this->bucket);
            }
        } catch (\Aws\S3\Exception\S3Exception $ex) {
            //echo 'EX: ' . $ex->getMessage() . PHP_EOL;
        }

    }

}
