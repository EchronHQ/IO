<?php
declare(strict_types = 1);
namespace Echron\IO;

abstract class AbstractIOClient
{
    abstract public function pull(string $remote, string $local);

    abstract public function push(string $local, string $remote);

    abstract public function getRemoteSize(string $remote):int;

    abstract public function getLocalSize(string $local):int;

    abstract public function getRemoteChangeDate(string $remote):int;

    abstract public function getLocalChangeDate(string $local):int;
}
