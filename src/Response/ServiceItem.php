<?php
declare(strict_types=1);


namespace Davidoc26\MojangAPI\Response;


class ServiceItem implements Item
{
    private string $name;
    private string $status;

    public function __construct(string $name, string $status)
    {
        $this->name = $name;
        $this->status = $status;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}