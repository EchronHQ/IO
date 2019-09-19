<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileType;
use Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Exception;
use GuzzleHttp\Psr7\Response;
use function class_exists;
use function is_null;
use const PHP_EOL;

/**
 * https://developers.google.com/drive/v3/web/quickstart/php
 * https://developers.google.com/api-client-library/php/start/get_started
 */
class GoogleDrive extends Base
{

    private $client, $service;

    public function __construct(string $accessToken = null)
    {
        if (!class_exists('\Google_Service_Drive')) {
            throw new Exception('google/apiclient package not installed');
        }
        $this->client = $this->getClient();
        if (!is_null($accessToken)) {
            $this->client->setAccessToken($accessToken);
        }
        $this->service = new Google_Service_Drive($this->client);
    }

    public function getAccessTokenStep1()
    {
        $authUrl = $this->getClient()
                        ->createAuthUrl();

        echo 'Auth: ' . $authUrl . PHP_EOL;
    }

    public function getAccessTokenStep2(string $authCode)
    {
        $accessToken = $this->getClient()
                            ->fetchAccessTokenWithAuthCode($authCode);
        //var_dump($accessToken);
        echo 'Accesstoken: ' . $accessToken['access_token'] . PHP_EOL;
    }

    private function getClient()
    {
        $p = 'echron_io.json';

        $client = new Google_Client();
        $client->setApplicationName('Echron IO lib');
        $client->setScopes([
            Google_Service_Drive::DRIVE_METADATA_READONLY,
            Google_Service_Drive::DRIVE,
        ]);
        $client->setDeveloperKey('AIzaSyAkE4lWB9PzxEcqkfTNV_AIHeeQqlQzzX4');

        //        $client->setAuthConfig('C:\Users\stijn\Documents\Dropbox\Projects\IO\Echron IO-776500f6e072.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setAuthConfig('credentials.json');
        //  $client->setAccessToken('AIzaSyAkE4lWB9PzxEcqkfTNV_AIHeeQqlQzzX4');

        //  Load previously authorized credentials from a file.
        //        $credentialsPath = expandHomeDirectory($p);
        //        if (file_exists($p)) {
        //            echo 'file exists';
        //            $accessToken = json_decode(file_get_contents($p), true);
        //        } else {
        //            //  Request authorization from the user.
        //
        //            if (false) {
        //            } else {
        //                // Exchange authorization code for an access token.
        //
        //                //                $this->setLocalFileContent($p, json_encode($accessToken));
        //                //                printf("Credentials saved to %s\n", $p);
        //            }
        //        }
        //        $client->setAccessToken($accessToken);
        //
        //        // Refresh the token if it's expired.
        //        if ($client->isAccessTokenExpired()) {
        //            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        //            file_put_contents($p, json_encode($client->getAccessToken()));
        //        }
        return $client;
    }

    public function push(string $local, string $remote, int $setRemoteChangeDate = null)
    {
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($remote);
        $contents = file_get_contents($local);

        $options = [
            'data'       => $contents,
            //            'mimeType'   => 'image/jpeg',
            'uploadType' => 'multipart',
            'fields'     => 'id',
        ];

        if (!is_null($setRemoteChangeDate)) {
            //
            $file->setModifiedTime($this->formatTime($setRemoteChangeDate));
        }

        $this->service->files->create($file, $options);
    }

    private function formatTime(int $time): string
    {
        return strftime('%Y-%m-%dT%H:%M:%SZ', $time);
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $stat = new FileStat($remote);
        $stat->setExists(false);

        $googleDriveFile = $this->getFileByName($remote);

        if (!is_null($googleDriveFile)) {
            $googleDriveFile = $this->service->files->get($googleDriveFile->getId(), ['fields' => 'size,modifiedTime']);
            $type = FileType::File();

            $bytes = intval($googleDriveFile->getSize());

            $changedate = intval(strtotime($googleDriveFile->getModifiedTime()));

            $stat->setExists(true);
            $stat->setBytes($bytes);
            $stat->setChangeDate($changedate);
            $stat->setType($type);
        }

        return $stat;
    }

    public function remoteFileExists(string $remote): bool
    {
        $file = $this->getFileByName($remote);

        return !is_null($file);
    }

    private function getFileByName(string $remote): ?Google_Service_Drive_DriveFile
    {
        //TODO: find a better way to get a file by name instead of getting all files in the Google Drive...
        $files = $this->service->files->listFiles();
        /** @var Google_Service_Drive_DriveFile $file */
        foreach ($files as $file) {
            //            $file->getParents()
            //            echo 'File: ' . $file->getName() . \PHP_EOL;
            if ($file->getName() === $remote) {
                return $file;
            }
        }

        return null;
    }

    public function pull(string $remote, string $local, int $localChangeDate = null)
    {
        try {
            $file = $this->getFileByName($remote);

            if (is_null($file)) {
                throw new Exception('Remote file does not exist');
            }

            /** @var Response $x */
            $x = $this->service->files->get($file->getId(), ['alt' => 'media']);
            $contents = $x->getBody()
                          ->getContents();

            $this->setLocalFileContent($local, $contents);

            if (!is_null($localChangeDate)) {
                $this->setLocalChangeDate($local, $localChangeDate);
            }
            //die('---');

            //var_dump($file);
        } catch (Google_Service_Exception $ex) {
            echo $ex->getMessage() . PHP_EOL;
        }
    }

    public function delete(string $remote)
    {
        $file = $this->getFileByName($remote);

        if (is_null($file)) {
            throw new Exception('Remote file does not exist');
        }
        $this->service->files->delete($file->getId());
    }

    public function setRemoteChangeDate(string $remote, int $changeDate)
    {
        // TODO: Implement setRemoteChangeDate() method.
        throw new Exception('Not implemented');
    }
}
