<?php
declare(strict_types=1);


use Davidoc26\MojangAPI\Collection\NameHistoryCollection;
use Davidoc26\MojangAPI\Collection\ServiceItemCollection;
use Davidoc26\MojangAPI\Exception\ForbiddenOperationException;
use Davidoc26\MojangAPI\Exception\IllegalArgumentException;
use Davidoc26\MojangAPI\MojangAPI;
use Davidoc26\MojangAPI\Response\AuthenticatedUserResponse;
use Davidoc26\MojangAPI\Response\ProfileInformationResponse;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ApiTest extends TestCase
{
    private MojangAPI $mojangAPI;

    private MockHandler $mockHandler;

    private object $userData;

    /**
     * @var \GuzzleHttp\Psr7\Request[]
     */
    private array $container;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();

        $this->container = [];

        $handlerStack = HandlerStack::create($this->mockHandler);
        $handlerStack->push(Middleware::history($this->container), 'history');

        $this->mojangAPI = new MojangAPI(['handler' => $handlerStack]);
        $this->userData = new class {
            public string $name = 'Steve';
            public string $uuid = '8667ba71b85a4004af54457a9734eed7';
            public string $skinUrl = 'http://textures.minecraft.net/texture/60a5bd016b3c9a1b9272e4929e30827a67be4ebb219017adbbc4a4d22ebd5b1';
            public string $capeUrl = 'http://textures.minecraft.net/texture/953cac8b779fe41383e675ee2b86071a71658f2180f56fbce8aa315ea70e2ed6';
            public string $value = 'ewogICJ0aW1lc3RhbXAiIDogMTYyNjE5OTE4MzY4NywKICAicHJvZmlsZUlkIiA6ICI4NjY3YmE3MWI4NWE0MDA0YWY1NDQ1N2E5NzM0ZWVkNyIsCiAgInByb2ZpbGVOYW1lIiA6ICJTdGV2ZSIsCiAgInRleHR1cmVzIiA6IHsKICAgICJTS0lOIiA6IHsKICAgICAgInVybCIgOiAiaHR0cDovL3RleHR1cmVzLm1pbmVjcmFmdC5uZXQvdGV4dHVyZS82MGE1YmQwMTZiM2M5YTFiOTI3MmU0OTI5ZTMwODI3YTY3YmU0ZWJiMjE5MDE3YWRiYmM0YTRkMjJlYmQ1YjEiCiAgICB9LAogICAgIkNBUEUiIDogewogICAgICAidXJsIiA6ICJodHRwOi8vdGV4dHVyZXMubWluZWNyYWZ0Lm5ldC90ZXh0dXJlLzk1M2NhYzhiNzc5ZmU0MTM4M2U2NzVlZTJiODYwNzFhNzE2NThmMjE4MGY1NmZiY2U4YWEzMTVlYTcwZTJlZDYiCiAgICB9CiAgfQp9';
        };
    }

    /**
     * @covers MojangAPI::apiStatus()
     */
    public function testApiStatus(): void
    {
        $this->mockHandler->append($this->createJsonResponse(200,
            [
                [
                    'minecraft.net' => 'green'
                ],
                [
                    'session.minecraft.net' => 'yellow'
                ],
                [
                    'account.mojang.com' => 'red'
                ],
            ]));
        $response = $this->mojangAPI->apiStatus();

        $this->assertEquals('https://status.mojang.com/check', $this->getCurrentUri());
        $this->assertInstanceOf(ServiceItemCollection::class, $response);
        $this->assertEquals(3, $response->count());
    }

    /**
     * @covers MojangAPI::getUuid()
     */
    public function testGetUuid(): void
    {
        $this->mockHandler->append($this->createJsonResponse(200,
            [
                'name' => $this->userData->name,
                'id' => $this->userData->uuid,
            ],
        ));
        $uuid = $this->mojangAPI->getUuid($this->userData->name);

        $this->assertEquals("https://api.mojang.com/users/profiles/minecraft/{$this->userData->name}", $this->getCurrentUri());
        $this->assertEquals($this->userData->uuid, $uuid);
    }

    /**
     * @covers MojangAPI::getUuid()
     *
     */
    public function testGetUuidNotFound(): void
    {
        $this->expectExceptionMessage("User {$this->userData->name} not found");
        $this->expectExceptionCode(204);
        $this->mockHandler->append($this->createJsonResponse(204, []));

        $this->mojangAPI->getUuid($this->userData->name);

        $this->assertEquals("https://api.mojang.com/users/profiles/minecraft/{$this->userData->name}", $this->getCurrentUri());
    }

    /**
     * @covers MojangAPI::getProfile()
     */
    public function testGetProfile(): void
    {
        $this->mockHandler->append($this->createJsonResponse(200,
            [
                'id' => $this->userData->uuid,
                'name' => $this->userData->name,
                'properties' => [
                    [
                        'value' => $this->userData->value,
                    ],
                ],
            ]));

        $response = $this->mojangAPI->getProfile($this->userData->uuid, true);

        $this->assertEquals("https://sessionserver.mojang.com/session/minecraft/profile/{$this->userData->uuid}", $this->getCurrentUri());
        $this->assertEquals($this->userData->name, $response->getName());
        $this->assertEquals($this->userData->uuid, $response->getUuid());
        $this->assertEquals($this->userData->skinUrl, $response->getSkinUrl());
        $this->assertEquals($this->userData->capeUrl, $response->getCapeUrl());
    }

    /**
     * @covers MojangAPI::usernamesToUuids()
     */
    public function testUsernamesToUuids(): void
    {
        $body =
            [
                [
                    'id' => '9b15dea6606e47a4a241420251703c59',
                    'name' => 'Foo',
                ],
                [
                    'id' => '14f19f5050cb44cd9f0bbe906ad59753',
                    'name' => 'Bar',
                ],
            ];
        $this->mockHandler->append($this->createJsonResponse(200, $body));

        $response = $this->mojangAPI->usernamesToUuids(['Foo', 'Bar']);

        $this->assertEquals("https://api.mojang.com/profiles/minecraft", $this->getCurrentUri());
        $this->assertIsArray($response);
    }


    /**
     * @covers MojangAPI::usernamesToUuids()
     */
    public function testUsernamesToUuidsWithManyUsernames(): void
    {
        $this->expectException(IllegalArgumentException::class);
        $this->mojangAPI->usernamesToUuids(range(0, 10));
    }


    /**
     * @covers MojangAPI::getNameHistory()
     */
    public function testGetNameHistory(): void
    {
        $this->mockHandler->append($this->createJsonResponse(200,
            [
                [
                    'name' => 'Foo',
                ],
                [
                    'name' => 'Bar',
                    'changedToAt' => 1414059749000,
                ],
            ]));

        $response = $this->mojangAPI->getNameHistory($this->userData->uuid);

        $this->assertEquals("https://api.mojang.com/user/profiles/{$this->userData->uuid}/names", $this->getCurrentUri());
        $this->assertInstanceOf(NameHistoryCollection::class, $response);
    }

    /**
     * @covers MojangAPI::authenticate()
     */
    public function testAuthenticate(): void
    {
        $this->mockHandler->append($this->createJsonResponse(200,
            [
                'clientToken' => 'someToken',
                'accessToken' => 'someToken',
                'selectedProfile' => [
                    'name' => $this->userData->name,
                    'id' => $this->userData->name,
                ],
            ]
        ));

        $response = $this->mojangAPI->authenticate('test@test', 'passwordTest');

        $this->assertEquals('https://authserver.mojang.com/authenticate', $this->getCurrentUri());
        $this->assertInstanceOf(AuthenticatedUserResponse::class, $response);
    }

    /**
     * @covers MojangAPI::authenticate()
     */
    public function testAuthenticateFail(): void
    {
        $this->expectException(ForbiddenOperationException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Invalid credentials. Invalid username or password.');

        $this->mockHandler->append($this->createJsonResponse(403,
            [
                'error' => 'ForbiddenOperationException',
                'errorMessage' => 'Invalid credentials. Invalid username or password.',
            ]
        ));
        $this->mojangAPI->authenticate('test@test', 'passwordTest');
    }

    /**
     * @covers MojangAPI::getProfileInformation()
     */
    public function testGetProfileInformation(): void
    {
        $this->mockHandler->append($this->createJsonResponse(200,
            [
                'id' => $this->userData->uuid,
                'name' => $this->userData->name,
                'skins' => [
                    'id' => 'someId',
                    'state' => 'ACTIVE',
                    'url' => $this->userData->skinUrl,
                    'variant' => 'CLASSIC'
                ],
                'capes' => [],
            ]
        ));

        $response = $this->mojangAPI->getProfileInformation('someToken');
        $this->assertEquals('https://api.minecraftservices.com/minecraft/profile', $this->getCurrentUri());
        $this->assertInstanceOf(ProfileInformationResponse::class, $response);
    }

    /**
     * @covers MojangAPI::nameAvailability()
     */
    public function testNameAvailabilityAvailable(): void
    {
        $this->mockHandler->append($this->createJsonResponse(200,
            [
                'status' => 'AVAILABLE'
            ],
        ));

        $availability = $this->mojangAPI->nameAvailability('Test', 'someToken');

        $this->assertEquals("https://api.minecraftservices.com/minecraft/profile/name/Test/available", $this->getCurrentUri());
        $this->assertTrue($availability);
    }

    /**
     * @covers MojangAPI::nameAvailability()
     */
    public function testNameAvailabilityDuplicate(): void
    {
        $this->mockHandler->append($this->createJsonResponse(200, [
            'status' => 'DUPLICATE'
        ]));

        $availability = $this->mojangAPI->nameAvailability('Test', 'someToken');

        $this->assertEquals('https://api.minecraftservices.com/minecraft/profile/name/Test/available', $this->getCurrentUri());
        $this->assertFalse($availability);
    }


    private function getCurrentUri(): string
    {
        return (string)$this->container[0]['request']->getUri();
    }

    private function createJsonResponse(int $status = 200, array $body = []): ResponseInterface
    {
        return new Response($status, [
            'Content-Type' => 'application/json',
        ], json_encode($body));
    }
}
