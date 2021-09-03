<?php
declare(strict_types=1);

namespace Davidoc26\MojangAPI;

use Davidoc26\MojangAPI\Collection\NameHistoryCollection;
use Davidoc26\MojangAPI\Collection\ServiceItemCollection;
use Davidoc26\MojangAPI\Exception\ForbiddenOperationException;
use Davidoc26\MojangAPI\Exception\IllegalArgumentException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Davidoc26\MojangAPI\Renderer\Renderer;
use Davidoc26\MojangAPI\Response\AuthenticatedUserResponse;
use Davidoc26\MojangAPI\Response\NameHistoryItem;
use Davidoc26\MojangAPI\Response\ProfileInformationResponse;
use Davidoc26\MojangAPI\Response\ProfileResponse;
use Davidoc26\MojangAPI\Response\ServiceItem;
use Davidoc26\MojangAPI\Response\UserResponse;
use Psr\Http\Message\ResponseInterface;


class MojangAPI
{
    private Client $client;

    public function __construct(array $config = [])
    {
        $this->client = new Client($config);
    }

    /**
     * Return status of various Mojang services.
     *
     * @link https://wiki.vg/Mojang_API#API_Status
     * @return ServiceItemCollection
     * @throws GuzzleException
     */
    public function apiStatus(): ServiceItemCollection
    {
        $response = $this->client->get('https://status.mojang.com/check');
        $response = $this->getDecodedResponse($response);

        $services = new ServiceItemCollection();
        foreach ($response as $service) {
            foreach ($service as $name => $status) {
                $services->add(new ServiceItem($name, $status));
            }
        }

        return $services;
    }

    /**
     * Return UUID by username.
     *
     * @link https://wiki.vg/Mojang_API#Username_to_UUID
     * @param string $nickname
     * @return string
     * @throws GuzzleException|IllegalArgumentException
     */
    public function getUuid(string $nickname): string
    {
        $response = $this->client->request('GET', "https://api.mojang.com/users/profiles/minecraft/$nickname");

        $uuid = @$this->getDecodedResponse($response)->id;

        if (empty($uuid)) {
            throw new IllegalArgumentException(sprintf("User %s not found", $nickname), $response->getStatusCode());
        }

        return $uuid;
    }

    /**
     * Return the player skin URL.
     *
     * @param string $uuid
     * @return string
     * @throws GuzzleException
     */
    public function getSkinUrl(string $uuid): string
    {
        return $this->getProfile($uuid)->getSkinUrl();
    }

    /**
     * Return the player's username plus any additional information about them (e.g. skins). <br>
     * If $decodeBase64 is true, the additional information will be decoded.
     *
     * @link https://wiki.vg/Mojang_API#UUID_to_Profile_and_Skin.2FCape
     * @param string $uuid
     * @param bool $decodeBase64
     * @return ProfileResponse
     * @throws GuzzleException
     */
    public function getProfile(string $uuid, bool $decodeBase64 = true): ProfileResponse
    {
        $response = $this->client->get(sprintf("https://sessionserver.mojang.com/session/minecraft/profile/%s", $uuid));

        $response = $this->getDecodedResponse($response);
        if ($decodeBase64) {
            $response->properties[0]->value = json_decode(base64_decode($response->properties[0]->value));
        }

        return new ProfileResponse($response);
    }

    /**
     * Return players UUIDs.
     *
     * @link https://wiki.vg/Mojang_API#Usernames_to_UUIDs
     * @param array $nicknames
     * @param bool $toUserResponse
     * @return UserResponse[]|array
     * @throws GuzzleException
     * @throws IllegalArgumentException
     */
    public function usernamesToUuids(array $nicknames, bool $toUserResponse = true): array
    {
        if (count($nicknames) > 10) {
            throw new IllegalArgumentException('Not more that 10 profile name per call is allowed.');
        }

        $response = ($this->client->post('https://api.mojang.com/profiles/minecraft', [
            'json' => $nicknames,
        ]))->getBody()->getContents();
        $response = json_decode($response);

        $users = [];
        foreach ($response as $item) {
            if ($toUserResponse) {
                $users[] = new UserResponse($this, $item->name, $item->id);
                continue;
            }
            $users[$item->name] = $item->id;
        }

        return $users;
    }

    /**
     * Get all names history by uuid.
     *
     * @link https://wiki.vg/Mojang_API#UUID_to_Name_History
     * @param string $uuid
     * @return NameHistoryCollection
     * @throws GuzzleException
     */
    public function getNameHistory(string $uuid): NameHistoryCollection
    {
        $response = $this->client->get(sprintf('https://api.mojang.com/user/profiles/%s/names', $uuid));
        $response = $this->getDecodedResponse($response);

        $namesHistory = new NameHistoryCollection();
        foreach ($response as $item) {
            $user = new NameHistoryItem($item->name, $item->changedToAt ?? null);
            $namesHistory->add($user);
        }

        return $namesHistory;
    }

    /**
     * Render player head from skin url.
     *
     * @param string $url
     * @param int $size
     * @param bool $onlyBase64
     * @return string
     * @see MojangAPI::getSkinUrl()
     */
    public function renderHead(string $url, int $size = 64, bool $onlyBase64 = false): string
    {
        return Renderer::renderHead($url, $size, $onlyBase64);
    }

    /**
     * Authenticates a user using their password.
     * @param string $email
     * @param string $password
     * @return AuthenticatedUserResponse
     * @throws ForbiddenOperationException
     * @link https://wiki.vg/Authentication#Authenticate
     */
    public function authenticate(string $email, string $password): AuthenticatedUserResponse
    {
        try {
            $response = $this->client->post('https://authserver.mojang.com/authenticate', [
                'json' => [
                    "agent" => [
                        "name" => "Minecraft",
                        "version" => 1,
                    ],
                    "username" => $email,
                    "password" => $password,
                ]
            ]);

        } catch (ClientException $exception) {
            $errorMessage = (json_decode($exception->getResponse()->getBody()->getContents()))->errorMessage;
            throw new ForbiddenOperationException($errorMessage, (int)$exception->getCode());
        }
        return new AuthenticatedUserResponse($this, $this->getDecodedResponse($response));
    }

    /**
     * Fetches information about the current account
     * @param string $token
     * @return ProfileInformationResponse
     * @throws GuzzleException
     * @link https://wiki.vg/Mojang_API#Profile_Information
     */
    public function getProfileInformation(string $token): ProfileInformationResponse
    {
        $response = $this->client->get('https://api.minecraftservices.com/minecraft/profile', [
            'headers' =>
                [
                    'Authorization' => 'Bearer ' . $token,
                ],
        ]);

        return new ProfileInformationResponse($this->getDecodedResponse($response));
    }

    /**
     * Check if the given name is available.
     * @param string $username
     * @param string $token
     * @return bool
     * @throws GuzzleException
     * @link https://wiki.vg/Mojang_API#Name_Availability
     */
    public function nameAvailability(string $username, string $token): bool
    {
        $response = $this->client->get(sprintf('https://api.minecraftservices.com/minecraft/profile/name/%s/available', $username), [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $response = $this->getDecodedResponse($response);

        if ($response->status === "DUPLICATE") {
            return false;
        }

        return true;
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    private function getDecodedResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents());
    }
}
