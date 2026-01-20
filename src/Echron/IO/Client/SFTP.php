<?php

declare(strict_types=1);

namespace Echron\IO\Client;

use Attlaz\Adapter\Base\Client\SFTP as SFTPClient;
use Attlaz\AttlazMonolog\Model\Exception\ContextualException;
use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Echron\IO\Data\FileType;
use Exception;
use function file_exists;
use function is_null;

class SFTP extends Base
{
    public function __construct(private SFTPClient $sftpClient)
    {

    }

    public function push(string $local, string $remote, int|null $setRemoteChangeDate = null): FileTransferInfo
    {
        if (!file_exists($local)) {
            throw new Exception('Unable to push, local file does not exist');
        }

//        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
//            $this->connect();
//        }
        //Create directory

        $directory = dirname($remote);

        //        var_dump($this->sftpClient->pwd() . ' ' . $directory);

        $sslc = $this->sftpClient->getSecondsSinceLastCommand();


        try {
            $dirCreated = $this->sftpClient->mkdir($directory, -1, true);
        } catch (\Throwable $ex) {
            throw new ContextualException('Unable to push file from `' . $local . '` to `' . $remote . '`: (unable to create remote dir `' . $directory . '`): ' . $ex->getMessage(), ['seconds since last command' => $sslc], 0, $ex);
        }


        //        var_dump($dirCreated);
        $sslc = $this->sftpClient->getSecondsSinceLastCommand();
        try {
            $fileIsUploaded = $this->sftpClient->put($remote, $local, SFTPClient::SOURCE_LOCAL_FILE);

        } catch (\Throwable $ex) {
            throw new ContextualException('Unable to push file from `' . $local . '` to `' . $remote . '`: ' . $ex->getMessage(), ['seconds since last command' => $sslc], 0, $ex);
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

    public function setRemoteChangeDate(string $remote, int $changeDate): bool
    {
//        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
//            $this->connect();
//        }

        return $this->sftpClient->touch($remote, $changeDate);
    }

    public function delete(string $remote): bool
    {
//        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
//            $this->connect();
//        }
        //Not recursive: return $this->getClient()->delete($remotePath, false);
        return $this->sftpClient->delete($remote);
    }

    public function remoteFileExists(string $remote): bool
    {
//        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
//            $this->connect();
//        }
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
//        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
//            $this->connect();
//        }

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
            throw new ContextualException('Unable to pull remote file "' . $remote . '"', ['seconds since last command' => $this->sftpClient->getSecondsSinceLastCommand(), 'SFTP last error' => $this->sftpClient->getLastSFTPError()]);
            //            var_dump($remote . ' => ' . $local);
        }

        if (!is_null($localChangeDate)) {
            $this->setLocalChangeDate($local, $localChangeDate);
        }

        // TODO: determine transferred bytes
        // return null;
        return new FileTransferInfo($fileIsDownloaded);
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
//        if ($this->sftpClient === null || !$this->sftpClient->isConnected()) {
//            $this->connect();
//        }
        $sslc = $this->sftpClient->getSecondsSinceLastCommand();
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
            throw new ContextualException('Unable to get remote file stats for `' . $remote . '`', ['seconds since last command' => $sslc], 0, $ex);
        }
    }

    public function getClient(): SFTPClient
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

    public function disconnect(): void
    {
        $this->sftpClient->disconnect();
    }

    /**
     * @inheritDoc
     */
    public function list(string $remotePath, bool $recursive = false): FileStatCollection
    {
//        if (!$this->sftpClient->isConnected()) {
//            $this->connect();
//        }

        $rawFiles = $this->sftpClient->rawlist($remotePath, $recursive);
        if ($rawFiles === false) {
            throw new Exception('Unable to get file list');
        }

        return $this->getFiles($remotePath, $rawFiles, $recursive);
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

    private function gluePath(string $part1, string $part2): string
    {
        // TODO: is forward slash the correct path separator?
        $pathSeparator = '/';

        if (str_ends_with($part1, $pathSeparator)) {
            return $part1 . $part2;
        }

        return $part1 . $pathSeparator . $part2;
    }
}
