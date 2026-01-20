<?php

declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Exception;

class Bridge extends Base
{
    private Base $master;
    private Base $slave;

    public function __construct(Base $master, Base $slave)
    {
        $this->master = $master;
        $this->slave = $slave;
    }

    public function getMaster(): Base
    {
        return $this->master;
    }

    public function getSlave(): Base
    {
        return $this->slave;
    }

    public function push(string $masterPath, string $slavePath, int|null $setRemoteChangeDate = null): FileTransferInfo
    {
        $tmpLocalPath = $this->getTempFilename();

        $this->master->pull($masterPath, $tmpLocalPath);
        $this->slave->push($tmpLocalPath, $slavePath, $setRemoteChangeDate);

        $this->removeLocal($tmpLocalPath);

        // TODO: determine transferred bytes
        return new FileTransferInfo(true);
    }

    private function getTempFilename(): string
    {
        return tempnam(sys_get_temp_dir(), 'io_bridge');
    }

    public function remoteFileExists(string $slavePath): bool
    {
        return $this->slave->remoteFileExists($slavePath);
    }

    public function getRemoteSize(string $remotePath): int
    {
        return $this->slave->getRemoteSize($remotePath);
    }

    public function getRemoteChangeDate(string $remotePath): int
    {
        return $this->slave->getRemoteChangeDate($remotePath);
    }

    public function pull(string $slavePath, string $masterPath, int|null $localChangeDate = null): FileTransferInfo
    {
        $tmpLocalPath = $this->getTempFilename();

        $this->slave->pull($slavePath, $tmpLocalPath);
        $this->master->push($tmpLocalPath, $masterPath, $localChangeDate);

        $this->removeLocal($tmpLocalPath);

        // TODO: determine transferred bytes
        return new FileTransferInfo(true);
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        return $this->slave->getRemoteFileStat($remote);
    }

    public function setRemoteChangeDate(string $slavePath, int $date): bool
    {
        return $this->slave->setRemoteChangeDate($slavePath, $date);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function getLocalFileStat(string $masterPath): FileStat
    {
        return $this->master->getRemoteFileStat($masterPath);
    }

    public function delete(string $remote): bool
    {
        return $this->slave->delete($remote);
    }

    public function moveRemoteFile(string $remoteSource, string $remoteDestination): void
    {
        throw new Exception('Not implemented');
    }

    public function getRemotePath(string $remotePath): string
    {
        return $remotePath;
    }

    public function localFileExists(string $masterPath): bool
    {
        return $this->master->remoteFileExists($masterPath);
    }

    public function setLocaleChangeDate(string $masterPath, int $date): bool
    {
        return $this->master->setRemoteChangeDate($masterPath, $date);
    }

    protected function _localFileExists(string $masterPath): bool
    {
        return $this->master->remoteFileExists($masterPath);
    }

    /**
     * @inheritDoc
     */
    public function list(string $remotePath, bool $recursive = false): FileStatCollection
    {
        throw new Exception('Not implemented');
    }
}
