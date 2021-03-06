<?php
declare(strict_types=1);


namespace Davidoc26\MojangAPI\Response;


use Davidoc26\MojangAPI\MojangAPI;
use GuzzleHttp\Exception\GuzzleException;

class UserResponse implements User
{
    protected MojangAPI $mojangAPI;
    protected string $name;
    protected string $uuid;

    public function __construct(MojangAPI $mojangAPI, string $name, string $uuid)
    {
        $this->mojangAPI = $mojangAPI;
        $this->name = $name;
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUuid(): string
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
