<?php
declare(strict_types=1);


namespace MojangAPI\Response;


class ProfileInformation
{
    private array $profile;

    public function __construct(array $profile)
    {
        $this->profile = $profile;
    }

    public function getUserUuid(): string
    {
        return $this->profile['id'];
    }

    public function getUsername(): string
    {
        return $this->profile['name'];
    }

    public function getSkinUrl(): string
    {
        return $this->profile['skins'][0]['url'];
    }
}