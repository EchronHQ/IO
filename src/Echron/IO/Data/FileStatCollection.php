<?php
declare(strict_types = 1);

namespace Echron\IO\Data;

class FileStatCollection implements \Iterator, \Countable
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

    public function current(): FileStat
    {
        return current($this->collection);
    }

    public function next()
    {
        next($this->collection);
    }

    public function key()
    {
        return key($this->collection);
    }

    public function valid()
    {
        return !!current($this->collection);
    }

    public function rewind()
    {
        reset($this->collection);
    }

    public function count()
    {
        return count($this->collection);
    }

}
