<?php
declare(strict_types=1);


namespace Davidoc26\MojangAPI\Collection;


use ArrayIterator;
use IteratorAggregate;
use Davidoc26\MojangAPI\Response\Item;
use Traversable;

abstract class Collection implements IteratorAggregate
{
    protected array $collection = [];

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->collection);
    }

    public function count(): int
    {
        return $this->getIterator()->count();
    }

    abstract public function add(Item $item): void;
}
