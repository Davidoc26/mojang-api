<?php
declare(strict_types=1);


namespace MojangAPI\Response;


use GuzzleHttp\Exception\GuzzleException;
use MojangAPI\MojangAPI;


class AuthenticatedUserResponse extends UserResponse
{
    private object $client;

    public function __construct(MojangAPI $mojangAPI, object $client)
    {
        parent::__construct($mojangAPI, $client->selectedProfile->name, $client->selectedProfile->id);
        $this->mojangAPI = $mojangAPI;
        $this->client = $client;
    }

    public function getAccessToken(): string
    {
        return $this->client->accessToken;
    }

    /**
     * @throws GuzzleException
     */
    public function nameAvailability(string $name): bool
    {
        return $this->mojangAPI->nameAvailability($name, $this->getAccessToken());
    }
}
