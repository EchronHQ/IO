<?php

declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Echron\IO\Data\FileType;
use Echron\Tools\FileSystem;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use function file_exists;
use function file_put_contents;
use function in_array;

abstract class Base implements LoggerAwareInterface
{
    protected LoggerInterface|null $logger = null;
    private array $localFileChanged = [];

    abstract public function push(string $local, string $remote, int|null $setRemoteChangeDate = null): FileTransferInfo;

    public function getLocalSize(string $local): int
    {
        return $this->getLocalFileStat($local)
            ->getBytes();
    }


    public function setLocalFileContent(string $local, string $contents): void
    {
        file_put_contents($local, $contents);

        $this->localFileChanged[] = $local;
    }

    public function flushLocalStatCache(string|null $local = null): void
    {
        clearstatcache(false, $local);
    }

    public function getLocalFileStat(string $local): FileStat
    {
        //TODO: we should not call this method every time we call this method
        if (in_array($local, $this->localFileChanged)) {
            $this->flushLocalStatCache($local);
        }

        $stat = new FileStat($local);
        if (file_exists($local)) {
            $fileModificationTime = filemtime($local);
            $fileSize = filesize($local);

            $stat->setExists(true);
            $stat->setChangeDate($fileModificationTime);
            $stat->setBytes($fileSize);
            //TODO: determine file type
            $stat->setType(FileType::File);
        }

        return $stat;
    }

    public function getRemoteChangeDate(string $remote): int
    {
        return $this->getRemoteFileStat($remote)
            ->getChangeDate();
    }

    abstract public function getRemoteFileStat(string $remote): FileStat;

    public function getRemoteSize(string $remote): int
    {
        return $this->getRemoteFileStat($remote)
            ->getBytes();
    }

    public function getLocalChangeDate(string $local): int
    {
        return $this->getLocalFileStat($local)
            ->getChangeDate();
    }

    final public function pullLazy(string $remote, string $local): FileTransferInfo
    {
        if (!$this->remoteFileExists($remote)) {
            throw new Exception('Unable to pull file: remote file `' . $remote . '` does not exist');
        }
        $remoteFileStat = $this->getRemoteFileStat($remote);
        $localFileStat = $this->getLocalFileStat($local);

        //TODO: when datetime is different or only when remote file is newer?
        if (!$localFileStat->equals($remoteFileStat)) {
            $downloadTransferInfo = $this->pull($remote, $local, $remoteFileStat->getChangeDate());
            $downloadTransferInfo->setLazyTransfer(true, true);
            return $downloadTransferInfo;
        }
        $result = new FileTransferInfo(true);
        $result->setLazyTransfer(true, false);

        return $result;


    }

    final public function pushLazy(string $local, string $remote): FileTransferInfo
    {
        if (!file_exists($local)) {
            throw new Exception('Unable to push file: local file `' . $local . '` does not exist');
        }
        $remoteFileStat = $this->getRemoteFileStat($remote);
        $localFileStat = $this->getLocalFileStat($local);

        //            echo 'Push lazy (' . $local . ' > ' . $remote . '):' . PHP_EOL . "\t" . 'Local:  ' . $localFileStat->debug() . PHP_EOL . "\t" . 'Remote: ' . $remoteFileStat->debug() . '' . PHP_EOL;
        if (!$remoteFileStat->equals($localFileStat)) {
            $uploadTransferInfo = $this->push($local, $remote, $localFileStat->getChangeDate());
            $uploadTransferInfo->setLazyTransfer(true, true);
            return $uploadTransferInfo;
        }
        $result = new FileTransferInfo(true);
        $result->setLazyTransfer(true, false);

        return $result;


    }

    abstract public function remoteFileExists(string $remote): bool;

    abstract public function pull(string $remote, string $local, int|null $localChangeDate = null): FileTransferInfo;

    public function setLocalChangeDate(string $local, int $changeDate): bool
    {
        try {
            FileSystem::touch($local, \DateTime::createFromFormat('U', (string)$changeDate));

            return true;
        } catch (\Throwable $ex) {
            return false;
        }
    }

    abstract public function delete(string $remote): bool;

    abstract public function setRemoteChangeDate(string $remote, int $changeDate): bool;

    public function removeLocal(string $local): bool
    {
        return unlink($local);
    }

    /**
     * @param string $remotePath
     * @param bool $recursive
     * @return FileStatCollection
     */
    abstract public function list(string $remotePath, bool $recursive = false): FileStatCollection;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
