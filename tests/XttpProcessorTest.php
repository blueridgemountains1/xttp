<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use JohnathanSmith\Xttp\XttpPending;
use JohnathanSmith\Xttp\XttpProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @group processor
 */
class XttpProcessorTest extends TestCase
{
    /** @var \JohnathanSmith\Xttp\XttpProcessor */
    private $processor;

    /** @var \JohnathanSmith\Xttp\XttpPending */
    private $xttpPending;

    public function setUp(): void
    {
        parent::setUp();
        $this->processor = new XttpProcessor();
        $this->xttpPending = XttpPending::new();
    }

    /**
     * @test
     */
    public function processes_guzzle_request_regular_string()
    {
        $container = [];

        $body = 'Hello, World';
        $headerKey = 'X-Foo';
        $headerVal = 'Bar';
        $url = 'https://johnathansmith.com';

        $mock = new MockHandler([
            new Response(200, [$headerKey => $headerVal], $body),
        ]);

        $r = $this->xttpPending
            ->withCookies(['John' => 'Smith'])
            ->withMock($mock)
            ->withHistory($container)
            ->setUrl($url)
            ->process();

        $this->assertEquals($body, $r->body());
        $this->assertEquals($headerVal, $r->header($headerKey));
        $this->assertEquals([$headerKey => $headerVal], $r->headers());

        $this->assertEquals($body, $r->body());

        $this->assertEquals('johnathansmith.com', $r->getEffectiveUri()->getHost());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Syntax error');
        $this->assertEquals($body, $r->json());
    }

    /** @test */
    public function processes_guzzle_request_as_json() {

        $body = [
            'johnathan' => 'smith',
            'xttp' => [
                [1,2,3],
                'response',
            ],
        ];

        $bodyJson = json_encode($body);

        $mock = new MockHandler([
            new Response(200, [], $bodyJson),
        ]);

        $r = $this->xttpPending
            ->withCookies(['John' => 'Smith'])
            ->withMock($mock)
            ->process();

        $this->assertEquals([], $r->headers());

        $this->assertEquals($body, $r->json());

        $this->assertEquals($bodyJson, $r->body());

    }
}
