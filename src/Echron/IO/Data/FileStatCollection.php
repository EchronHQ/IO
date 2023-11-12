<?php

declare(strict_types=1);

namespace Echron\IO\Data;

class FileStatCollection implements \IteratorAggregate, \Countable
{
    /** @var FileStat[] */
    private array $collection;

    public function __construct()
    {
        $this->collection = [];
    }

    public function add(FileStat $fileStat): void
    {
        $this->collection[] = $fileStat;
    }

    /**
     * @return FileStat[]
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->collection);
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public static function sortByName(FileStatCollection $collection): FileStatCollection
    {
        $data = $collection->getIterator()
            ->getArrayCopy();

        \uasort($data, function (FileStat $a, FileStat $b): int {
            return strcmp($a->getPath(), $b->getPath());
        });

        $result = new FileStatCollection();

        foreach ($data as $item) {
            $result->add($item);
        }

        return $result;
    }

    public static function totalBytes(FileStatCollection $collection): int
    {
        $total = 0;
        foreach ($collection as $item) {
            $total += $item->getBytes();
        }

        return $total;
    }

}
