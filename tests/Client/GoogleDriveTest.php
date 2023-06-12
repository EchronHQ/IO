<?php

declare(strict_types=1);
require_once 'AbstractTest.php';

class GoogleDriveTest extends AbstractTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $client = $this->getClient();


        if (!$client->available()) {
            $this->markTestSkipped(
                'GoogleDrive not available'
            );
        }


    }

    protected function getClient(): \Echron\IO\Client\GoogleDrive
    {
        $accessToken = 'ya29.GluIB4pbI4iDx0Tz36vEbkZwyox8HDj4bsgX11AKRI_L_NRv1VctzZf31KpKqSrt53ncbd_dc9P5qzAVnwoBjzkTGWIHEFn9yWKdF1y918eJcJWvMIBgZ9FTuRh2';
        $client = new \Echron\IO\Client\GoogleDrive($accessToken);
        // $client->getAccessTokenStep1();

        //        $client->getAccessTokenStep2('4/rAEdx8Hwyt-3PcEb9H6WtspJBp5E5m-izmHTA9dSc9ASj9H0zXGdM5o');
        //        die('---');

        return $client;
    }

    protected function getRemoteTestFilePath(): string
    {
        return 'testfile_' . uniqid() . '.txt';
    }

    protected function getRemoteTestFileContent(): string
    {
        return '';
    }

}
