<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileType;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use phpseclib\Net\SFTP as SFTPClient;

/**
 * https://github.com/kunalvarma05/dropbox-php-sdk/wiki/Usage
 */
class Dropbox extends Base
{
    private $clientId, $clientSecret, $accessToken;
    /** @var DropboxApp DropboxApp */
    private $dropboxClient;

    public function __construct(string $clientId, string $clientSecret, string $accessToken = null)
    {
        parent::__construct();
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $accessToken;

        $dropboxApp = new DropboxApp($this->clientId, $this->clientSecret, $this->accessToken);
        $this->dropboxClient = new \Kunnu\Dropbox\Dropbox($dropboxApp);

        $this->capabilities->setCanPush(true);
        $this->capabilities->setCanPull(true);
        $this->capabilities->setCanChangeModifyDate(true);
        $this->capabilities->setCanCopy(true);
        $this->capabilities->setCanDelete(true);
        $this->capabilities->setCanMove(true);

        //TODO: authenticate application

//        $authHelper = $this->dropboxClient->getAuthHelper();
//
//        $callbackUrl = "https://echron.be/login-callback.php";
//        $authUrl = $authHelper->getAuthUrl($callbackUrl);
//        echo $authUrl;

        // $authHelper->getAccessToken($code, $stat, $callbackUrl);

//Callback URL
        // $callbackUrl = "https://{my-website}/login-callback.php";
    }

    public function push(string $local, string $remote)
    {
        $file = new DropboxFile($local);
        //TODO: file must start with / to refer as root
        /**
         * https://www.dropbox.com/developers/documentation/http/documentation#files-upload
         */

        $modificationTime = $this->getLocalChangeDate($local);

        $options = [
            'autorename'      => false,
            'client_modified' => $this->formatTime($modificationTime),
            'mute'            => false,
        ];

        $metadata = $this->dropboxClient->upload($file, $remote, $options);
    }

    private function formatTime(int $time): string
    {

        return strftime('%Y-%m-%dT%H:%M:%SZ', $time);
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $fileStat = new FileStat($remote);

        $metaData = null;
        try {
            $metaData = $this->dropboxClient->getMetadata($remote);
            $fileStat->setExists(true);
        } catch (\Exception $ex) {
            $fileStat->setExists(false);
        }

        if ($metaData !== null) {
            $fileStat->setBytes($metaData->getSize());

            $modification = $metaData->getClientModified();
            $fileStat->setChangeDate(strtotime($modification));

            $dropboxType = $metaData->getDataProperty('.tag');

            $type = FileType::Unknown();
            switch ($dropboxType) {
                case 'file':
                    $type = FileType::File();
                    break;
                default:
                    throw new \Exception('Unknown dropbox type "' . $dropboxType . '"');
            }
            $fileStat->setType($type);
        }

        return $fileStat;
    }

    public function remoteFileExists(string $remote): bool
    {
        return $this->getRemoteFileStat($remote)
                    ->getExists();
    }

    public function delete(string $remote)
    {
        $this->dropboxClient->delete($remote);
    }

    public function pull(string $remote, string $local)
    {
        /**
         * https://www.dropbox.com/developers/documentation/http/documentation#files-download
         */
        $file = $this->dropboxClient->download($remote);
        $contents = $file->getContents();
        file_put_contents($local, $contents);

    }

    public function setRemoteChangeDate(string $remote, int $changeDate)
    {
        //TODO: how to implement?
        throw new \Exception('Not implemented');
    }
}
