<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Psr\SimpleCache\CacheInterface;

class Cache extends Base
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;

    }

    public function push(string $local, string $remote): bool
    {

        $key = $this->formatName($remote);
        $statKey = $key . '_stat';

        $data = \file_get_contents($local);
        $data = \base64_encode($data);

        $this->cache->set($key, $data);

        $stat = $this->getLocalFileStat($local);

        $this->setRemoteFileStat($remote, $stat);

        return true;

    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $key = $this->formatName($remote);
        $statKey = $key . '_stat';

        if ($this->cache->has($statKey)) {
            return $this->cache->get($statKey);
        } else {
            $stat = new FileStat($remote);
            $stat->setExists(false);

            return $stat;
        }

    }

    public function remoteFileExists(string $remote): bool
    {
        $fileStat = $this->getRemoteFileStat($remote);

        return $fileStat->getExists();
    }

    public function pull(string $remote, string $local)
    {
        $key = $this->formatName($remote);

        $data = $this->cache->get($key);
        $data = \base64_decode($data);

        \file_put_contents($local, $data);

    }

    public function delete(string $remote)
    {
        $key = $this->formatName($remote);
        $statKey = $key . '_stat';
        $this->cache->delete($key);
        $this->cache->delete($statKey);

    }

    public function setRemoteChangeDate(string $remote, int $changeDate)
    {
        $key = $this->formatName($remote);
        $statKey = $key . '_stat';

        $stat = $this->getRemoteFileStat($remote);

        $stat->setChangeDate($changeDate);

        $this->setRemoteFileStat($remote, $stat);

        // TODO: Implement setRemoteChangeDate() method.
    }

    private function setRemoteFileStat(string $remote, FileStat $stat)
    {
        $key = $this->formatName($remote);
        $statKey = $key . '_stat';

        $this->cache->set($statKey, $stat);
    }

    private function formatName(string $input): string
    {


        return \sha1($input);

        return $input;
    }
}
