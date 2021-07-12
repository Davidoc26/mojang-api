<?php
declare(strict_types=1);


namespace MojangAPI\Collection;


use Traversable;

abstract class Collection implements \IteratorAggregate
{
    abstract public function getIterator(): Traversable;
}