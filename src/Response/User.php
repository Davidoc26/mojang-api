<?php
declare(strict_types=1);


namespace MojangAPI\Response;


class User
{
    private string $name;
    private ?string $uuid;

    public function __construct(string $name, ?string $uuid = null)
    {
        $this->name = $name;
        $this->uuid = $uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}