<?php
declare(strict_types=1);


namespace Davidoc26\MojangAPI\Response;


use Davidoc26\MojangAPI\MojangAPI;
use GuzzleHttp\Exception\GuzzleException;


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
