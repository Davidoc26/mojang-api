<?php
declare(strict_types=1);


namespace MojangAPI\Response;


class ProfileResponse implements User
{
    private object $profile;

    public function __construct(object $profile)
    {
        $this->profile = $profile;
    }

    public function getName(): string
    {
        return $this->profile->name;
    }

    public function getUuid(): string
    {
        return $this->profile->id;
    }

    public function getSkinUrl(): string
    {
        return $this->profile->properties[0]->value->textures->SKIN->url;
    }

    public function getCapeUrl(): ?string
    {
        return $this->profile->properties[0]->value->textures->CAPE->url;
    }
}