<?php
declare(strict_types=1);

namespace Echron\IO\Data;

class FileStatCollection implements \IteratorAggregate, \Countable
{
    private $collection;

    public function __construct()
    {
        $this->collection = [];
    }

    public function add(FileStat $fileStat)
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

    public function count()
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

}
