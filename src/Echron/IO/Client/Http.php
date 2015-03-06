<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Echron\IO\AbstractIOClient;

class Http extends AbstractIOClient
{

    public function pull(string $remote, string $local)
    {
        // TODO: Implement pull() method.
    }

    public function push(string $local, string $remote)
    {
        // TODO: Implement push() method.
    }

    public function getRemoteSize(string $remote): int
    {
        // TODO: Implement getRemoteSize() method.
    }

    public function getLocalSize(string $local): int
    {
        // TODO: Implement getLocalSize() method.
    }

    public function getRemoteChangeDate(string $remote): int
    {
        // TODO: Implement getRemoteChangeDate() method.
    }

    public function getLocalChangeDate(string $local): int
    {
        // TODO: Implement getLocalChangeDate() method.
    }
}
