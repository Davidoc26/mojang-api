<?php
declare(strict_types=1);


namespace MojangAPI\Collection;


use MojangAPI\Response\ServiceItem;
use Traversable;

class ServiceItemCollection extends Collection
{
    /**
     * @var ServiceItem[]
     */
    private array $services;

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->services);
    }

    public function add(ServiceItem $serviceItem): void
    {
        $this->services[] = $serviceItem;
    }
}