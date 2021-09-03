<?php
declare(strict_types=1);


namespace Davidoc26\MojangAPI\Collection;


use Davidoc26\MojangAPI\Response\Item;
use Davidoc26\MojangAPI\Response\NameHistoryItem;

class NameHistoryCollection extends Collection
{
    public function add(Item $item): void
    {
        if (!$item instanceof NameHistoryItem) {
            throw new \InvalidArgumentException();
        }

        $this->collection[] = $item;
    }

    public function sortByChangedToAt(): self
    {
        usort($this->collection, function (NameHistoryItem $first, NameHistoryItem $second) {
            if ($first->getChangedToAt() === $second->getChangedToAt()) {
                return 0;
            }

            return ($first->getChangedToAt() > $second->getChangedToAt()) ? -1 : 1;
        });

        return $this;
    }
}
