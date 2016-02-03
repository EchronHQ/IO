<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;

abstract class Base
{
    abstract public function pull(string $remote, string $local);

    abstract public function push(string $local, string $remote);

    abstract public function getRemoteSize(string $remote): int;

    public final function getLocalSize(string $local): int
    {
        return $this->getLocalFileStat($local)
                    ->getBytes();
    }

    private function getLocalFileStat(string $local): FileStat
    {
        $stat = new FileStat($local);
        if (file_exists($local)) {
            $fileModificationTime = filemtime($local);
            $fileSize = filesize($local);

            $stat->setChangeDate($fileModificationTime);
            $stat->setBytes($fileSize);

        }

        return $stat;
    }

    abstract public function getRemoteChangeDate(string $remote): int;

    public final function getLocalChangeDate(string $local): int
    {
        return $this->getLocalFileStat($local)
                    ->getChangeDate();
    }
}
