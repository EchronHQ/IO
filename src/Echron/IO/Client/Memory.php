<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Exception;
use function is_null;

class Memory extends Base
{
    private $files;

    public function __construct()
    {
        $this->files = [];
    }

    public function push(string $local, string $remote, int $setRemoteChangeDate = null)
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

    public function delete(string $remote)
    {
        $hashedName = $this->hashFileName($remote);
        if (isset($this->files[$hashedName])) {
            unset($this->files[$hashedName]);
        }
    }

    public function pull(string $remote, string $local, int $localChangeDate = null)
    {
        if ($this->remoteFileExists($remote)) {
            $file = $this->getFile($remote);
            $contents = $file['data'];

            $this->setLocalFileContent($local, $contents);
            if (!is_null($localChangeDate)) {
                $this->setLocalChangeDate($local, $localChangeDate);
            }
        } else {
            throw new Exception('Unable to pull file: remote "' . $remote . '" does not exist');
        }
    }

    public function setRemoteChangeDate(string $remote, int $changeDate)
    {
        $file = $this->getFile($remote);
        /** @var FileStat $stat */
        $stat = $file['stat'];
        $stat->setChangeDate($changeDate);
    }
}
