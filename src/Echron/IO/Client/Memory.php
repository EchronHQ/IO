<?php
declare(strict_types = 1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;

class Memory extends Base
{
    private $files;

    public function __construct()
    {
        parent::__construct();
        $this->files = [];

        $this->capabilities->setCanPush(true);
        $this->capabilities->setCanPull(true);
        $this->capabilities->setCanChangeModifyDate(true);
        $this->capabilities->setCanCopy(true);
        $this->capabilities->setCanDelete(true);
        $this->capabilities->setCanMove(true);
    }

    public function push(string $local, string $remote)
    {
        //TODO: test if local exist
        $hashRemote = $this->hashFileName($remote);

        $data = file_get_contents($local);

        $localFileStat = $this->getLocalFileStat($local);

        $fileStat = new FileStat($remote);
        $fileStat->setBytes($localFileStat->getBytes());
        $fileStat->setChangeDate($localFileStat->getChangeDate());
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
        throw new \Exception('File "' . $name . '" does not exist');
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

    public function pull(string $remote, string $local)
    {
        if ($this->remoteFileExists($remote)) {
            $file = $this->getFile($remote);
            $data = $file['data'];

            file_put_contents($local, $data);
        } else {
            throw new \Exception('Unable to pull file: remote "' . $remote . '" does not exist');
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
