<?php
declare(strict_types=1);


namespace MojangAPI\Collection;


use InvalidArgumentException;
use MojangAPI\Response\Item;
use MojangAPI\Response\ServiceItem;

class ServiceItemCollection extends Collection
{
    public function add(Item $item): void
    {
        if (!$item instanceof ServiceItem) {
            throw new InvalidArgumentException();
        }

        $this->collection[] = $item;
    }

    public function sortByName($desc = false): self
    {
        usort($this->collection, function ($first, $second) use ($desc) {
            if ($desc) {
                return strcmp($second->getName(), $first->getName());
            }
            return strcmp($first->getName(), $second->getName());
        });

        return $this;
    }

    public function sortByStatus($desc = false): self
    {
        usort($this->collection, function ($first, $second) {
            $firstName = strtolower($first->getStatus());
            $secondName = strtolower($second->getStatus());

            if ($firstName !== $secondName) {
                if ($firstName === 'green') {
                    return -1;
                }
                if ($firstName === 'yellow' && $secondName === 'green') {
                    return 1;
                }
                if ($firstName === 'red') {
                    return 1;
                }
            }
            return 0;
        });

        if ($desc) {
            $this->collection = array_reverse($this->collection);
        }

        return $this;
    }
}