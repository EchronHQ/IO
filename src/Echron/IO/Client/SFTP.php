<?php

declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Echron\IO\Data\FileType;
use Echron\IO\Helper\FileHelper;
use Echron\Tools\StringHelper;
use Exception;
use phpseclib3\Crypt\Common\AsymmetricKey;
use phpseclib3\Crypt\RSA;
use phpseclib3\Net\SFTP as SFTPClient;
use function file_exists;
use function file_get_contents;
use function is_null;

class SFTP extends Base
{
    private string $host;
    private string|null $username = null;
    private string|AsymmetricKey|null $password = null;
    private int $port;
    private int $timeout;
    private SFTPClient|null $sftpClient = null;

    public function __construct(string $host, int $port = 22, int $timeout = 30)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function loginWithPassword(string $username, string $password = null): void
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function loginWithKey(string $username, string $keyFilePath, string|null $keyFilePassword = null): void
    {
        if (!file_exists($keyFilePath)) {
            $maskedKeyFile = FileHelper::maskFileName($keyFilePath);

            throw new Exception('Key file "' . $maskedKeyFile . '" does not exist');
        }

        if (!is_null($keyFilePassword)) {
            $key = RSA::load(file_get_contents($keyFilePath), $keyFilePassword);
        } else {
            $key = RSA::load(file_get_contents($keyFilePath));
        }

        $this->username = $username;
        $this->password = $key;
    }

    private function connect(): void
    {
        if ($this->sftpClient !== null) {
            $this->sftpClient->disconnect();
        }
        $this->sftpClient = new SFTPClient($this->host, $this->port, $this->timeout);
        if ($this->username === null) {
            throw new Exception('Username must be defined');
        }
        if ($this->password === null) {
            throw new Exception('Password must be defined');
        }
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

        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
            $this->connect();
        }
        //Create directory

        $directory = dirname($remote);

        //        var_dump($this->sftpClient->pwd() . ' ' . $directory);

        try {
            $dirCreated = $this->sftpClient->mkdir($directory, -1, true);
        } catch (\Throwable $ex) {
            throw new Exception('Unable to push file from `' . $local . '` to `' . $remote . '`: unable to create remote dir `' . $directory . '`', 0, $ex);
        }


        //        var_dump($dirCreated);

        try {
            $fileIsUploaded = $this->sftpClient->put($remote, $local, SFTPClient::SOURCE_LOCAL_FILE);
        } catch (\Throwable $ex) {
            throw new Exception('Unable to push file from `' . $local . '` to `' . $remote . '`: sftp put error', 0, $ex);
        }


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
        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
            $this->connect();
        }
        try {


            $sftpType = $this->sftpClient->filetype($remote);

            $stat = new FileStat($remote);
            $stat->setExists(false);

            if ($sftpType) {
                $type = $this->parseSFTPTypeToFileType($sftpType);

                //TODO: separate when only 1 of the stats is needed
                //TODO: try  $this->sftpClient->stat()

                $bytes = (int)$this->sftpClient->filesize($remote);
                $changedate = (int)$this->sftpClient->filemtime($remote);

                $stat->setExists(true);
                $stat->setBytes($bytes);
                $stat->setChangeDate($changedate);
                $stat->setType($type);
            }

            return $stat;
        } catch (Exception $ex) {
            throw new Exception('Unable to get remote file stats for `' . $remote . '`', 0, $ex);
        }
    }

    private function parseSFTPTypeToFileType(string $sftpType): FileType
    {
        //        if (!$this->sftpClient->isConnected()) {
        //            $this->connectClient();
        //        }
        return match ($sftpType) {
            'file' => FileType::File,
            'dir' => FileType::Dir,
            default => throw new Exception('Unknown SFTP file type "' . $sftpType . '"'),
        };
    }

    public function delete(string $remote): bool
    {
        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
            $this->connect();
        }
        //Not recursive: return $this->getClient()->delete($remotePath, false);
        return $this->sftpClient->delete($remote);
    }

    public function remoteFileExists(string $remote): bool
    {
        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
            $this->connect();
        }
        $this->sftpClient->disableStatCache();

        return $this->sftpClient->file_exists($remote);
    }

    public function pull(
        string $remote,
        string $local,
        int    $localChangeDate = null,
        bool   $showProgress = false
    ): FileTransferInfo
    {
        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
            $this->connect();
        }

        $progress = null;
        if ($showProgress) {
            $stats = $this->getRemoteFileStat($remote);
            $progress = static function ($read) use ($stats) {
                if ($read > 0) {
                    $percent = \round($read / $stats->getBytes() * 100, 2);
                    echo $percent . '%' . \PHP_EOL;
                } else {
                    echo '0%' . \PHP_EOL;
                }
            };
        }
        $fileIsDownloaded = $this->sftpClient->get($remote, $local, 0, -1, $progress);

        if (!$fileIsDownloaded) {
            throw new Exception('Unable to pull remote file "' . $remote . '" (' . $this->sftpClient->getLastSFTPError() . ')');
            //            var_dump($remote . ' => ' . $local);
        }

        if (!is_null($localChangeDate)) {
            $this->setLocalChangeDate($local, $localChangeDate);
        }

        // TODO: determine transferred bytes
        // return null;
        return new FileTransferInfo($fileIsDownloaded);
    }

    public function setRemoteChangeDate(string $remote, int $changeDate): bool
    {
        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
            $this->connect();
        }

        return $this->sftpClient->touch($remote, $changeDate);
    }


    public function disconnect(): void
    {
        if ($this->sftpClient !== null) {
            $this->sftpClient->disconnect();
        }
    }

    public function getClient(): SFTPClient|null
    {
        return $this->sftpClient;
    }

    public function setClient(SFTPClient $sftpClient, bool $disconnectIfClientExists = true): void
    {
        if ($disconnectIfClientExists && !is_null($this->sftpClient) && $this->sftpClient->isConnected()) {
            $this->sftpClient->disconnect();
        }
        $this->sftpClient = $sftpClient;
    }

    /**
     * @inheritDoc
     */
    public function list(string $remotePath, bool $recursive = false): FileStatCollection
    {
        if (!$this->sftpClient->isConnected()) {
            $this->connect();
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
            }
        }

        return $result;
    }

    private function gluePath(string $part1, string $part2): string
    {
        // TODO: is forward slash the correct path separator?
        $pathSeparator = '/';

        if (StringHelper::endsWith($part1, $pathSeparator)) {
            return $part1 . $part2;
        }

        return $part1 . $pathSeparator . $part2;
    }

    private function rawFileToFileStat(array $rawFile, string $remotePath): ?FileStat
    {
        if ($rawFile['filename'] === '.' || $rawFile['filename'] === '..') {
            return null;
        }

        $path = $this->gluePath($remotePath, $rawFile['filename']);

        $fileStat = new FileStat($path);

        $rawType = $rawFile['type'];
        $type = FileType::Unknown;
        switch ($rawType) {
            case 1:
                $type = FileType::File;
                break;
            case 2:
                $type = FileType::Dir;
                break;
            default:
                echo 'Unknown file type ' . $rawType . \PHP_EOL;
        }

        //\var_dump($this->sftpClient->file_types);
        // $type = $this->parseSFTPTypeToFileType($this->sftpClient->filetype($path));

        $bytes = (int)$rawFile['size'];
        $changedate = (int)$rawFile['mtime'];

        $fileStat->setExists(true);
        $fileStat->setBytes($bytes);
        $fileStat->setChangeDate($changedate);
        $fileStat->setType($type);

        return $fileStat;
    }


}
