<?php
declare(strict_types=1);

namespace MojangAPI;

use MojangAPI\Exception\IllegalArgumentException;
use MojangAPI\Renderer\Renderer;
use MojangAPI\Response\NameHistoryUser;
use MojangAPI\Response\Service;
use MojangAPI\Response\User;


class MojangAPI
{
    /**
     * Return status of various Mojang services.
     *
     * @link https://wiki.vg/Mojang_API#API_Status
     * @return array
     */
    public static function apiStatus(): array
    {
        $response = self::request('https://status.mojang.com/check');
        $response = json_decode($response);
        $services = [];
        foreach ($response as $item) {
            foreach ($item as $name => $status) {
                $services[] = new Service($name, $status);
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
     */
    public static function getUuid(string $nickname): string
    {
        $response = self::request("https://api.mojang.com/users/profiles/minecraft/$nickname");
        return json_decode($response)->id;
    }

    /**
     * Return the player skin URL.
     *
     * @param string $uuid
     * @return string
     * @link
     */
    public static function getSkinUrl(string $uuid): string
    {
        $response = self::getProfile($uuid);
        return $response->properties[0]->value->textures->SKIN->url;
    }

    /**
     * Return the player's username plus any additional information about them (e.g. skins). <br>
     * If $decodeBase64 is true, the additional information will be decoded.
     *
     * @link https://wiki.vg/Mojang_API#UUID_to_Profile_and_Skin.2FCape
     * @param string $uuid
     * @param bool $decodeBase64
     * @return mixed
     */
    public static function getProfile(string $uuid, bool $decodeBase64 = true)
    {
        $response = self::request("https://sessionserver.mojang.com/session/minecraft/profile/$uuid");
        $response = json_decode($response);
        if ($decodeBase64) {
            $response->properties[0]->value = json_decode(base64_decode($response->properties[0]->value));
        }
        return $response;
    }

    /**
     * Return players UUIDs.
     *
     * @link https://wiki.vg/Mojang_API#Usernames_to_UUIDs
     * @param $nicknames
     * @return array
     * @throws IllegalArgumentException
     */
    public static function usernamesToUuids($nicknames): array
    {
        if (count($nicknames) > 10) {
            throw new IllegalArgumentException('Not more that 10 profile name per call is allowed.');
        }
        $response = self::request("https://api.mojang.com/profiles/minecraft", true, $nicknames);
        $response = json_decode($response);
        $users = [];
        foreach ($response as $item) {
            $users[] = new User($item->name, $item->id);
        }
        return $users;
    }

    /**
     * Get all names history by uuid.
     *
     * @link https://wiki.vg/Mojang_API#UUID_to_Name_History
     * @param string $uuid
     * @return array
     */
    public static function getNameHistory(string $uuid): array
    {
        $response = self::request("https://api.mojang.com/user/profiles/$uuid/names");
        $response = json_decode($response);
        $names = [];
        foreach ($response as $item) {
            $item = (array)$item;
            $user = new NameHistoryUser($item['name'], $item['changedToAt'] ?? null);
            $names[] = $user;
        }
        return $names;
    }

    /**
     * Render player head from skin url.
     *
     * @param string $url
     * @param int $size
     * @return string
     * @see MojangAPI::getSkinUrl()
     */
    public static function renderHead(string $url, int $size = 64): string
    {
        return Renderer::renderHead($url, $size);
    }

    private static function request(string $url, bool $isPost = false, array $data = null)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($isPost) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_POST, true);
        }

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
