<?php
declare(strict_types=1);


namespace MojangAPI\Response;


class NameHistoryResponse implements User
{
    private string $name;
    private ?int $changedToAt;

    public function __construct(string $name, ?int $changedToAt = null)
    {
        $this->name = $name;
        $this->changedToAt = $changedToAt ? $changedToAt / 1000 : null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getChangedToAt(): ?int
    {
        return $this->changedToAt;
    }

}
