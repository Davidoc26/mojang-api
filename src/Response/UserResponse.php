<?php
declare(strict_types=1);


namespace MojangAPI\Response;


use GuzzleHttp\Exception\GuzzleException;
use MojangAPI\MojangAPI;

class UserResponse implements User
{
    protected MojangAPI $mojangAPI;
    protected string $name;
    protected ?string $uuid;

    public function __construct(MojangAPI $mojangAPI, string $name, ?string $uuid = null)
    {
        $this->mojangAPI = $mojangAPI;
        $this->name = $name;
        $this->uuid = $uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @throws GuzzleException
     */
    public function getSkinUrl(): string
    {
        return $this->mojangAPI->getSkinUrl($this->getUuid());
    }

    /**
     * @throws GuzzleException
     */
    public function renderHead(int $size = 64, bool $onlyBase64 = false): string
    {
        return $this->mojangAPI->renderHead($this->getSkinUrl(), $size, $onlyBase64);
    }
}
