<?php
declare(strict_types = 1);
namespace Echron\IO;

abstract class AbstractIOClient
{
    abstract public function pull(string $remote, string $local);

    abstract public function push(string $local, string $remote);
}
