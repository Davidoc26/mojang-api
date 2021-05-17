<?php
declare(strict_types=1);


namespace MojangAPI\Response;


class AuthenticatedUser
{
    private array $client;

    public function __construct(array $client)
    {
        $this->client = $client;
    }

    public function getAccessToken(): string
    {
        return $this->client['accessToken'];
    }

    public function getUsername(): string
    {
        return $this->client['selectedProfile']['name'];
    }

    public function getUserUuid(): string
    {
        return $this->client['selectedProfile']['id'];
    }
}