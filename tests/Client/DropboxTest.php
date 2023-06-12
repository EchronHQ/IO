<?php

declare(strict_types=1);
require_once 'AbstractTest.php';

class DropboxTest extends AbstractTest
{
    private $appKey = '85iwawrxvz2cbly';
    private $appSecret = 'vxcaomv7ap599qi';
    private $expiredAccessToken = 'yTeTDvUWwucAAAAAAACtITnaKlaXvfiyfZn4NwOmITZrhcYhAHFNuQ5ucag3jTII';
    private $accessToken = 'yTeTDvUWwucAAAAAAAJR9a5DF5RPFTrsrbyIQi1Y6FukuwJSM-wHglhs_4Gs0qe8';

    protected function getClient(): \Echron\IO\Client\Base
    {
        $client = new \Echron\IO\Client\Dropbox($this->appKey, $this->appSecret, $this->accessToken);

        /**
         * If the access token is expired
         *
         * STEP 1:
         * request the access token auth url with
         *
         * $client->getAccessTokenStep1();
         *
         * STEP 2
         * Copy the state and code to following parameters and call
         *
         * $state = '30c1be3acfa525413cfd277b699fd235';
         * $code = 'yTeTDvUWwucAAAAAAAJR9NIhpqzkyTtl0OQR1H80BHE';
         * $client->getAccessTokenStep2($state, $code);
         *
         * This will output the new access token that you should set in this class
         */

        return $client;
    }

    protected function getRemoteTestFilePath(): string
    {
        return '/testfile_' . uniqid() . '.txt';
    }

    protected function getRemoteTestFileContent(): string
    {
        // TODO: Implement getRemoteTestFileContent() method.
    }
}
