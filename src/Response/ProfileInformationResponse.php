<?php
declare(strict_types=1);


namespace Davidoc26\MojangAPI\Response;


class ProfileInformationResponse implements User
{
    private object $profile;

    public function __construct(object $profile)
    {
        $this->profile = $profile;
    }

    public function getUserUuid(): string
    {
        return $this->profile->id;
    }

    public function getName(): string
    {
        return $this->profile->name;
    }

    public function getSkinUrl(): string
    {
        return $this->profile->skins[0]->url;
    }
}