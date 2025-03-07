<?php

declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Echron\IO\Data\FileType;
use Exception;
use Psr\SimpleCache\CacheInterface;
use function base64_decode;
use function base64_encode;
use function file_exists;
use function file_get_contents;
use function is_null;
use function sha1;

class Cache extends Base
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function push(string $local, string $remote, int $setRemoteChangeDate = null): FileTransferInfo
    {
        $key = $this->formatName($remote);
        $statKey = $key . '_stat';

        if (!file_exists($local)) {
            throw new Exception('Unable to save file to cache: file "' . $local . '" doesn\'t exist');
        }
        $data = file_get_contents($local);
        $data = base64_encode($data);

        $this->cache->set($key, $data);

        $stat = $this->getLocalFileStat($local);
        if (!is_null($setRemoteChangeDate)) {
            $stat->setChangeDate($setRemoteChangeDate);
        }

        $this->setRemoteFileStat($remote, $stat);

        // TODO: determine transferred bytes
        return new FileTransferInfo(true);
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $key = $this->formatName($remote);
        $statKey = $key . '_stat';

        if ($this->cache->has($statKey)) {
            $result = $this->cache->get($statKey);

            $stat = new FileStat($result['path'], FileType::from($result['type']));
            $stat->setExists($result['exists']);
            $stat->setChangeDate($result['changeDate']);
            $stat->setBytes($result['bytes']);

            return $stat;
        }
        $stat = new FileStat($remote);
        $stat->setExists(false);

        return $stat;
    }

    public function remoteFileExists(string $remote): bool
    {
        return $this->getRemoteFileStat($remote)->getExists();
    }

    public function pull(string $remote, string $local, int $localChangeDate = null): FileTransferInfo
    {
        $key = $this->formatName($remote);

        $data = $this->cache->get($key);
        $data = base64_decode($data);

        $this->setLocalFileContent($local, $data);

        if (!is_null($localChangeDate)) {
            $this->setLocalChangeDate($local, $localChangeDate);
        }

        // TODO: determine transferred bytes
        return new FileTransferInfo(true);
    }

    public function delete(string $remote): bool
    {
        $key = $this->formatName($remote);
        $statKey = $key . '_stat';
        $this->cache->delete($key);
        $this->cache->delete($statKey);

        return true;
    }

    public function setRemoteChangeDate(string $remote, int $changeDate): bool
    {
        $key = $this->formatName($remote);
        $statKey = $key . '_stat';

        $stat = $this->getRemoteFileStat($remote);

        $stat->setChangeDate($changeDate);

        $this->setRemoteFileStat($remote, $stat);

        // TODO: Implement setRemoteChangeDate() method.
        return true;
    }

    /**
     * @inheritDoc
     */
    public function list(string $remotePath, bool $recursive = false): FileStatCollection
    {
        throw new Exception('Not implemented');
    }

    private function setRemoteFileStat(string $remote, FileStat $stat): void
    {
        $key = $this->formatName($remote);
        $statKey = $key . '_stat';

        $data = [
            'path' => $stat->getPath(),
            'type' => $stat->getType()->value,
            'exists' => $stat->getExists(),
            'changeDate' => $stat->getChangeDate(),
            'bytes' => $stat->getBytes(),

        ];
        $this->cache->set($statKey, $data);
    }

    private function formatName(string $input): string
    {
        return sha1($input);
    }
}
