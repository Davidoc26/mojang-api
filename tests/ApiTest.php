<?php
declare(strict_types=1);


use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use MojangAPI\MojangAPI;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    private MojangAPI $mojangAPI;

    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $this->mojangAPI = new MojangAPI(['handler' => $handlerStack]);
    }
}
