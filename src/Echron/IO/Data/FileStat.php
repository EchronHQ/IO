<?php
declare(strict_types = 1);
namespace Echron\IO\Data;

class FileStat
{
    private $path = '', $bytes = -1, $changeDate = -1;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setBytes(int $bytes)
    {
        $this->bytes = $bytes;
    }

    public function setChangeDate(int $changeDate)
    {
        $this->changeDate = $changeDate;
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }

    public function getChangeDate(): int
    {
        return $this->changeDate;
    }
}
