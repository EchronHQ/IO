<?php

declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Echron\IO\Data\FileType;
use Exception;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Kunnu\Dropbox\Models\FileMetadata;
use Kunnu\Dropbox\Models\FolderMetadata;
use function class_exists;
use function is_null;
use const PHP_EOL;

/**
 * https://github.com/kunalvarma05/dropbox-php-sdk/wiki/Usage
 */
class Dropbox extends Base
{
    private $clientId;
    private $clientSecret;
    private $accessToken;
    /** @var DropboxApp DropboxApp */
    private $dropboxClient;

    private $callbackUrl = "https://echron.be/login-callback.php";

    public function __construct(string $clientId, string $clientSecret, string|null $accessToken = null)
    {
        if (!class_exists('\Kunnu\Dropbox\DropboxApp')) {
            throw new Exception('kunalvarma05/dropbox-php-sdk package not installed');
        }
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $accessToken;

        $dropboxApp = new DropboxApp($this->clientId, $this->clientSecret, $this->accessToken);

        $this->dropboxClient = new \Kunnu\Dropbox\Dropbox($dropboxApp);
    }

    public function getAccessTokenStep1(): void
    {
        $authHelper = $this->dropboxClient->getAuthHelper();
        $authUrl = $authHelper->getAuthUrl($this->callbackUrl);

        $state = $authHelper->getPersistentDataStore()
            ->get('state');
        echo 'State: ' . $state . PHP_EOL;
        echo 'Auth url: ' . $authUrl . PHP_EOL;
    }

    public function getAccessTokenStep2(string $state, string $code): void
    {
        $authHelper = $this->dropboxClient->getAuthHelper();

        $authHelper->getPersistentDataStore()
            ->set('state', $state);
        $accessToken = $authHelper->getAccessToken($code, $state, $this->callbackUrl);
        echo 'AccessToken: ' . $accessToken->getToken() . PHP_EOL;
    }

    public function push(string $local, string $remote, int|null $setRemoteChangeDate = null): FileTransferInfo
    {
        $file = new DropboxFile($local);
        //TODO: file must start with / to refer as root
        /**
         * https://www.dropbox.com/developers/documentation/http/documentation#files-upload
         */

        $options = [
            'autorename' => false,
            // 'client_modified' => $this->formatTime($modificationTime),
            'mute' => false,
        ];

        if (!is_null($setRemoteChangeDate)) {
            $options['client_modified'] = $this->formatTime($setRemoteChangeDate);
        }

        $metadata = $this->dropboxClient->upload($file, $remote, $options);

        // TODO: determine transferred bytes
        return new FileTransferInfo(true);
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $fileStat = new FileStat($remote);
        $fileStat->setExists(false);

        $metaData = $this->getMetaData($remote);

        if (!is_null($metaData)) {
            $fileStat->setExists(true);
            $fileStat->setBytes($metaData->getSize());

            $modification = $metaData->getClientModified();
            $fileStat->setChangeDate(strtotime($modification));

            $dropboxType = $metaData->getDataProperty('.tag');

            $type = match ($dropboxType) {
                'file' => FileType::File,
                default => throw new Exception('Unknown Dropbox type "' . $dropboxType . '"'),
            };
            $fileStat->setType($type);
        }

        return $fileStat;
    }

    public function remoteFileExists(string $remote): bool
    {
        return $this->getRemoteFileStat($remote)
            ->getExists();
    }

    public function delete(string $remote): bool
    {
        $metaData = $this->getMetaData($remote);
        if (!is_null($metaData)) {
            $this->dropboxClient->delete($remote);
        }

        return true;
    }

    public function pull(string $remote, string $local, int|null $localChangeDate = null): FileTransferInfo
    {
        /**
         * https://www.dropbox.com/developers/documentation/http/documentation#files-download
         */
        $file = $this->dropboxClient->download($remote);
        $contents = $file->getContents();
        $this->setLocalFileContent($local, $contents);
        if (!is_null($localChangeDate)) {
            $this->setLocalChangeDate($local, $localChangeDate);
        }

        // TODO: determine transferred bytes
        return new FileTransferInfo(true);
    }

    public function setRemoteChangeDate(string $remote, int $changeDate): bool
    {
        //TODO: how to implement?
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     */
    public function list(string $remotePath, bool $recursive = false): FileStatCollection
    {
        throw new Exception('Not implemented');
    }

    private function formatTime(int $time): string
    {
        return date('%Y-%m-%dT%H:%M:%SZ', $time);
    }

    /**
     * @param string $remote
     * @return FileMetadata|FolderMetadata|null
     */
    private function getMetaData(string $remote): FileMetadata|FolderMetadata|null
    {
        try {
            return $this->dropboxClient->getMetadata($remote);
        } catch (DropboxClientException $ex) {
            //TODO: check the exception message, should be something like:
            //{"error_summary": "path/not_found/...", "error": {".tag": "path", "path": {".tag": "not_found"}}} 409
        }

        return null;
    }
}
