<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileType;
use Echron\Tools\FileSystem;

abstract class Base
{

    abstract public function push(string $local, string $remote);

    public function getLocalSize(string $local): int
    {
        return $this->getLocalFileStat($local)
                    ->getBytes();
    }

    public function getLocalFileStat(string $local): FileStat
    {
        $stat = new FileStat($local);
        if (file_exists($local)) {
            $fileModificationTime = filemtime($local);
            $fileSize = filesize($local);

            $stat->setChangeDate($fileModificationTime);
            $stat->setBytes($fileSize);
            //TODO: determine file type
            $stat->setType(FileType::File());

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

    public final function pullLazy(string $remote, string $local)
    {

        if (!$this->remoteFileExists($remote)) {
            throw new \Exception('Unable to pull file: remote file `' . $remote . '` does not exist');
        } else {
            $remoteFileStat = $this->getRemoteFileStat($remote);
            $localFileStat = $this->getLocalFileStat($local);

            //TODO: when datetime is different or only when remote file is newer?
            if (!$localFileStat->equals($remoteFileStat)) {
                $downloaded = $this->pull($remote, $local);
                if ($downloaded) {
                    $this->setLocalChangeDate($local, $remoteFileStat->getChangeDate());

                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }

    abstract public function remoteFileExists(string $remote): bool;

    abstract public function pull(string $remote, string $local);

    public function setLocalChangeDate(string $local, int $changeDate)
    {
        FileSystem::touch($local, \DateTime::createFromFormat('U', $changeDate));
    }

    abstract public function setRemoteChangeDate(string $remote, int $changeDate);

    public function removeLocal(string $local): bool
    {
        return unlink($local);
    }

}
