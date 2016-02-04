<?php
declare(strict_types = 1);
namespace Echron\IO\Data;

class FileStat
{
    private $path = '', $bytes = -1, $changeDate = -1, $exists = false;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function setExists(bool $exists)
    {
        $this->exists = $exists;
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

    public function equals(FileStat $fileStat): bool
    {
        $equals = true;
        if ($this->getExists() !== $fileStat->getExists()) {
            $equals = false;
        }
        if ($this->getChangeDate() !== $fileStat->getChangeDate()) {
            $equals = false;
        }
        if ($this->getBytes() . '' !== $fileStat->getBytes() . '') {
            $equals = false;
        }

        return $equals;
    }

    public function getExists(): bool
    {
        return $this->exists;
    }

    public function getChangeDate(): int
    {
        return $this->changeDate;
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }
}
