<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Echron\IO\Data\FileType;
use Echron\Tools\StringHelper;
use Exception;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP as SFTPClient;
use function file_exists;
use function file_get_contents;
use function is_null;

class SFTP extends Base
{
    private $host, $username, $password, $port, $timeout = 30;
    /** @var  SFTPClient */
    private $sftpClient;

    public function __construct(string $host, int $port = 22)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function loginWithPassword(string $username, string $password = null)
    {
        $this->username = $username;
        $this->password = $password;
        //TODO: lazy connection
        $this->initClient();
    }

    public function loginWithKey(string $username, string $keyFilePath, string $keyFilePassword = null)
    {
        if (!file_exists($keyFilePath)) {
            throw new Exception('Key file does not exist');
        }

        if (!is_null($keyFilePassword)) {
            $key = RSA::load(file_get_contents($keyFilePath), $keyFilePassword);
        } else {
            $key = RSA::load(file_get_contents($keyFilePath), $keyFilePassword);
        }

        $this->username = $username;
        $this->password = $key;
        //TODO: lazy connection
        $this->initClient();
    }

    private function initClient()
    {
        $this->connectClient();
    }

    private function connectClient()
    {
        if (!is_null($this->sftpClient)) {
            $this->sftpClient->disconnect();
        }
        $this->sftpClient = new SFTPClient($this->host, $this->port, $this->timeout);
        $authenticated = $this->sftpClient->login($this->username, $this->password);
        if (!$authenticated) {
            $this->sftpClient = null;
            throw new Exception('Unable to login: not authenticated 2 `' . $this->username . '@' . $this->host . ':' . $this->port . '`');
        }
    }

    public function push(string $local, string $remote, int $setRemoteChangeDate = null): FileTransferInfo
    {
        if (!file_exists($local)) {
            throw new Exception('Unable to push, local file does not exist');
        }

        if (!$this->sftpClient->isConnected()) {
            $this->connectClient();
        }
        //Create directory

        $directory = dirname($remote);

        //        var_dump($this->sftpClient->pwd() . ' ' . $directory);
        $dirCreated = $this->sftpClient->mkdir($directory, -1, true);

        //        var_dump($dirCreated);
        $fileIsUploaded = $this->sftpClient->put($remote, $local, SFTPClient::SOURCE_LOCAL_FILE);

        if (!is_null($setRemoteChangeDate)) {
            $this->setRemoteChangeDate($remote, $setRemoteChangeDate);
        }

        // TODO: determine transferred bytes
        return new FileTransferInfo($fileIsUploaded);

        //        $res = $this->sftpClient->put($remote, $local, SFTPClient::SOURCE_LOCAL_FILE, -1, -1, function ($sftp_packet_size) {
        //            echo 'X: ' . $sftp_packet_size . \PHP_EOL;
        //
        //        });
        //        var_dump($res);

    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        if (!$this->sftpClient->isConnected()) {
            $this->connectClient();
        }
        $sftpType = $this->sftpClient->filetype($remote);

        $stat = new FileStat($remote);
        $stat->setExists(false);

        if ($sftpType) {
            $type = $this->parseSFTPTypeToFileType($sftpType);

            //TODO: separate when only 1 of the stats is needed
            //TODO: try  $this->sftpClient->stat()

            $bytes = intval($this->sftpClient->filesize($remote));
            $changedate = intval($this->sftpClient->filemtime($remote));

            $stat->setExists(true);
            $stat->setBytes($bytes);
            $stat->setChangeDate($changedate);
            $stat->setType($type);
        }

        return $stat;
    }

    private function parseSFTPTypeToFileType(string $sftpType): FileType
    {
        //        if (!$this->sftpClient->isConnected()) {
        //            $this->connectClient();
        //        }
        $parsedType = FileType::Unknown();
        switch ($sftpType) {
            case 'file':
                $parsedType = FileType::File();
                break;
            case 'dir':
                $parsedType = FileType::Dir();
                break;
            default:
                throw new Exception('Unknown SFTP file type "' . $sftpType . '"');
        }

        return $parsedType;
    }

    public function delete(string $remote): bool
    {
        if (!$this->sftpClient->isConnected()) {
            $this->connectClient();
        }

        return $this->sftpClient->delete($remote);
    }

    public function remoteFileExists(string $remote): bool
    {
        if (!$this->sftpClient->isConnected()) {
            $this->connectClient();
        }
        $this->sftpClient->disableStatCache();

        return $this->sftpClient->file_exists($remote);
    }

    public function pull(string $remote, string $local, int $localChangeDate = null): FileTransferInfo
    {
        if (!$this->sftpClient->isConnected()) {
            $this->connectClient();
        }

        $fileIsDownloaded = $this->sftpClient->get($remote, $local);

        if (!$fileIsDownloaded) {
            throw new Exception('Unable to pull remote file "' . $remote . '" (' . $this->sftpClient->getLastSFTPError() . ')');
            //            var_dump($remote . ' => ' . $local);
        }

        if (!is_null($localChangeDate)) {
            $this->setLocalChangeDate($local, $localChangeDate);
        }

        // TODO: determine transferred bytes
        return new FileTransferInfo($fileIsDownloaded);
    }

    public function setRemoteChangeDate(string $remote, int $changeDate): bool
    {
        if (!$this->sftpClient->isConnected()) {
            $this->connectClient();
        }

        return $this->sftpClient->touch($remote, $changeDate);
    }

    public function getClient(): SFTPClient
    {
        return $this->sftpClient;
    }

    /**
     * @inheritDoc
     */
    public function list(string $remotePath, bool $recursive = false): FileStatCollection
    {
        if (!$this->sftpClient->isConnected()) {
            $this->connectClient();
        }

        //\var_dump($remotePath);
        $rawFiles = $this->sftpClient->rawlist($remotePath, $recursive);
        if ($rawFiles === false) {
            throw new Exception('Unable to get file list');
        }

        return $this->getFiles($remotePath, $rawFiles, $recursive);
    }

    private function getFiles(string $remotePath, array $rawFiles, bool $recursive): FileStatCollection
    {
        $result = new FileStatCollection();
        foreach ($rawFiles as $key => $rawFile) {
            if (\is_object($rawFile) || !$recursive) {
                $fileStat = $this->rawFileToFileStat((array)$rawFile, $remotePath);
                if (!is_null($fileStat)) {
                    $result->add($fileStat);
                }
            } elseif (\is_array($rawFile)) {
                $path = $this->gluePath($remotePath, (string)$key);
                $subFiles = $this->getFiles($path, $rawFile, $recursive);
                foreach ($subFiles as $subFile) {
                    $result->add($subFile);
                }
            } else {
            }
        }

        return $result;
    }

    private function gluePath($part1, string $part2): string
    {
        // TODO: is forward slash the correct path separator?
        $pathSeparator = '/';

        if (StringHelper::endsWith($part1, $pathSeparator)) {
            return $part1 . $part2;
        }

        return $part1 . $pathSeparator . $part2;;
    }

    private function rawFileToFileStat(array $rawFile, string $remotePath): ?FileStat
    {
        if ($rawFile['filename'] === '.' || $rawFile['filename'] === '..') {
            return null;
        }

        $path = $this->gluePath($remotePath, $rawFile['filename']);

        $fileStat = new FileStat($path);

        $rawType = $rawFile['type'];
        $type = FileType::Unknown();
        switch ($rawType) {
            case 1:
                $type = FileType::File();
                break;
            case 2:
                $type = FileType::Dir();
                break;
            default:
                echo 'Unknown file type ' . $rawType . \PHP_EOL;
        }

        //\var_dump($this->sftpClient->file_types);
        // $type = $this->parseSFTPTypeToFileType($this->sftpClient->filetype($path));

        $bytes = intval($rawFile['size']);
        $changedate = intval($rawFile['mtime']);

        $fileStat->setExists(true);
        $fileStat->setBytes($bytes);
        $fileStat->setChangeDate($changedate);
        $fileStat->setType($type);

        return $fileStat;
    }

    /**
     * @inheritDoc
     */
    public function deleteFile(string $remotePath): bool
    {
        if (!$this->sftpClient->isConnected()) {
            $this->connectClient();
        }

        return $this->getClient()
                    ->delete($remotePath, false);
    }
}
