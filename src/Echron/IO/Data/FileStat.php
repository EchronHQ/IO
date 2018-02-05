<?php
declare(strict_types=1);

namespace Echron\IO\Data;

class FileStat
{
    private $path = '', $bytes = -1, $changeDate = -1, $exists = false, $type;

    public function __construct(string $path, FileType $type = null)
    {
        if (empty(trim($path))) {
            throw new \InvalidArgumentException('FileStat path cannot be empty');
        }
        $this->path = $path;

        if (is_null($type)) {
            $type = FileType::Unknown();
        }
        $this->type = $type;
    }

    public function setType(FileType $type): void
    {
        $this->type = $type;
    }

    public function setExists(bool $exists): void
    {
        $this->exists = $exists;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setBytes(int $bytes): void
    {
        $this->bytes = $bytes;
    }

    public function setChangeDate(int $changeDate): void
    {
        $this->changeDate = $changeDate;
    }

    public function equals(FileStat $fileStat): bool
    {
        if ($this->getType() !== $fileStat->getType()) {
            return false;
        }
        if ($this->getExists() !== $fileStat->getExists()) {
            return false;
        }
        if ($this->getChangeDate() !== $fileStat->getChangeDate()) {
            return false;
        }
        if ($this->getBytes() . '' !== $fileStat->getBytes() . '') {
            return false;
        }

        return true;
    }

    public function getType(): FileType
    {
        return $this->type;
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

    public function debug(): string
    {
        $output = [];
        if ($this->changeDate !== -1) {
            $output[] = 'Changedate: ' . date("Y-m-d H:i:s", $this->changeDate);
        } else {
            $output[] = 'Changedate: unknown';
        }
        $output[] = 'Exists: ' . ($this->exists ? 'Y' : 'N');

        return \implode(' - ', $output);
    }
}
