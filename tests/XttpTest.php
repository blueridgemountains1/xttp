<?php

namespace JohnathanSmith\Xttp\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use JohnathanSmith\Xttp\Xttp;
use PHPUnit\Framework\TestCase;

/**
 * @group xttp
 */
class XttpTest extends TestCase
{
    /**
     * @var string
     */
    private $url;

    public function setUp(): void
    {
        parent::setUp();
        $this->url = 'https://johnathansmith.com';
    }

    /**
     * @test
     * @dataProvider xttpProviders
     */
    public function post_test(string $method)
    {
        $mock = new MockHandler([
            new Response(200),
        ]);

        $stack = new HandlerStack($mock);

        $container = [];

        $history = Middleware::history($container);

        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $response = Xttp::{$method}($this->url, [], $client);

        $this->assertEquals(strtoupper($method), $container[0]['request']->getMethod());

        $this->assertEquals($this->url, (string) $container[0]['request']->getUri());

        $this->assertEquals(200, $response->status());
    }

    public function xttpProviders()
    {
        return [
            'post' => ['post'],
            'get' => ['get'],
            'patch' => ['patch'],
            'put' => ['put'],
            'delete' => ['delete'],
        ];
    }
}
