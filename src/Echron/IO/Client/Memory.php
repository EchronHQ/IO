<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Exception;
use function is_null;

class Memory extends Base
{
    private array $files;

    public function __construct()
    {
        $this->files = [];
    }

    public function push(string $local, string $remote, int $setRemoteChangeDate = null): FileTransferInfo
    {
        //TODO: test if local exist
        $hashRemote = $this->hashFileName($remote);

        $data = file_get_contents($local);

        $localFileStat = $this->getLocalFileStat($local);

        $fileStat = new FileStat($remote);
        $fileStat->setExists(true);
        $fileStat->setBytes($localFileStat->getBytes());
        if (!is_null($setRemoteChangeDate)) {
            $fileStat->setChangeDate($setRemoteChangeDate);
        } else {
            $fileStat->setChangeDate($this->getLocalChangeDate($local));
        }

        $fileStat->setType($localFileStat->getType());

        $this->files[$hashRemote] = [
            'stat' => $fileStat,
            'data' => $data,

        ];

        // TODO: determine transferred bytes
        return new FileTransferInfo(true);
    }

    private function hashFileName(string $file): string
    {
        return base64_encode($file);
    }

    private function getFile(string $name): array
    {
        $hashedName = $this->hashFileName($name);
        if (isset($this->files[$hashedName])) {
            return $this->files[$hashedName];
        }
        throw new Exception('File "' . $name . '" does not exist');
    }

    public function remoteFileExists(string $remote): bool
    {
        $hashedName = $this->hashFileName($remote);

        return isset($this->files[$hashedName]);
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        if ($this->remoteFileExists($remote)) {
            $file = $this->getFile($remote);

            return $file['stat'];
        }

        $fileStat = new FileStat($remote);
        $fileStat->setExists(false);

        return $fileStat;
    }

    public function delete(string $remote): bool
    {
        $hashedName = $this->hashFileName($remote);
        if (isset($this->files[$hashedName])) {
            unset($this->files[$hashedName]);

            return true;
        }

        return false;
    }

    public function pull(string $remote, string $local, int $localChangeDate = null): FileTransferInfo
    {
        if ($this->remoteFileExists($remote)) {
            $file = $this->getFile($remote);
            $contents = $file['data'];

            $this->setLocalFileContent($local, $contents);
            if (!is_null($localChangeDate)) {
                $this->setLocalChangeDate($local, $localChangeDate);
            }

            // TODO: determine transferred bytes
            return new FileTransferInfo(true);
        } else {
            throw new Exception('Unable to pull file: remote "' . $remote . '" does not exist');
        }
    }

    public function setRemoteChangeDate(string $remote, int $changeDate): bool
    {
        $file = $this->getFile($remote);
        /** @var FileStat $stat */
        $stat = $file['stat'];
        $stat->setChangeDate($changeDate);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function list(string $remotePath, bool $recursive = false): FileStatCollection
    {
        throw new Exception('Not implemented');
    }
}
