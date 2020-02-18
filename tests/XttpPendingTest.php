<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use JohnathanSmith\Xttp\Tests\TestHelpers;
use JohnathanSmith\Xttp\XttpPending;
use PHPUnit\Framework\TestCase;

/**
 * @group pending
 */
class XttpPendingTest extends TestCase
{
    /** @var \JohnathanSmith\Xttp\XttpPending */
    private $pending;

    public function setUp(): void
    {
        parent::setUp();
        $this->pending = XttpPending::new();
    }

    /**
     * @test
     */
    public function withOptions_will_replace_options()
    {
        $options = ['headers' => ['X-Foo' => 'Bar']];

        $this->assertNotEquals($options, $this->pending->getOptions());

        $newOptions = $this->pending
            ->setOptions($options)
            ->getOptions();

        $this->assertSame($options, $newOptions);
    }

    /**
     * @test
     * @dataProvider optionsDataProvider
     */
    public function can_run_option_functions(callable $fn, string $key, $value)
    {
        $this->pending = $fn($this->pending);

        $options = $this->pending->getOptions();

        $this->assertEquals($value, $options[$key]);
    }

    /**
     * @test
     * @dataProvider headerDataProvider
     */
    public function can_run_header_functions(callable $fn, array $checks)
    {
        $this->pending = $this->pending->setHeaders([]);

        $this->pending = $fn($this->pending);

        $headers = $this->pending->getHeaders();

        foreach ($checks as $key => $expected) {
            $this->assertEquals($expected, $headers[$key]);
        }
    }

    /**
     * @test
     */
    public function can_append_header()
    {
        $originalHeaders = $this->pending->getHeaders();

        $header = ['Johnathan' => 'Smith'];

        $newHeaders = $this->pending->addHeader($header)->getHeaders();

        $this->assertEquals(array_merge($originalHeaders, $header), $newHeaders);
    }

    /**
     * @test
     */
    public function withHeaders_can_set_headers()
    {
        $headers = ['Johnathan' => 'Smith'];

        $actual = $this->pending
            ->setHeaders($headers)
            ->getHeaders();

        $this->assertEquals('Smith', $actual['Johnathan']);
    }

    /**
     * @test
     * @group w
     */
    public function bodyFormat_can_be_set()
    {
        $expected = 'form_params';

        $actual = $this->pending
            ->asFormParams()
            ->getBodyFormat();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function can_get_and_set_urls()
    {
        $expected = 'http://johnathansmith.com/';

        $actual = $this->pending
            ->setUrl($expected)
            ->getUrl();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider asTypeProviders
     *
     * @param  \Closure  $fn
     * @param  string  $bodyFormat
     * @param  string|null  $type
     */
    public function type_providers_set_successfully(Closure $fn, string $bodyFormat, string $contentType = null)
    {
        $this->pending = $fn($this->pending);

        $actualBodyFormat = $this->pending->getBodyFormat();

        $this->assertEquals($bodyFormat, $actualBodyFormat);

        if ($contentType !== null) {
            $actualContentType = $this->pending->getContentType();
            $this->assertEquals($contentType, $actualContentType);
        }
    }

    public function asTypeProviders()
    {
        return [
            'asMultipart' => [
                function (XttpPending $pending) {
                    return $pending->asMultipart();
                }, 'multipart', null,
            ],
            'asFormParams' => [
                function (XttpPending $pending) {
                    return $pending->asFormParams();
                }, 'form_params', 'application/x-www-form-urlencoded',
            ],
            'asJson' => [
                function (XttpPending $pending) {
                    return $pending->asJson();
                }, 'json', 'application/json',
            ],
        ];
    }

    /**
     * @test
     */
    public function setOptions_can_set_options()
    {
        $expected = ['Johnathan' => 'Smith'];

        $actual = $this->pending->setOptions($expected)->getOptions();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function addOption_can_append_options()
    {
        $originalOptions = ['Foo' => 'Bar', 'X-Bar' => 'Foo'];

        $newOption = ['Foo' => 'Baz', 'X-Foo' => 'X-Bar'];

        $actual = $this->pending
            ->setOptions($originalOptions)
            ->addOption($newOption)
            ->getOptions();

        $expected = ['Foo' => 'Baz', 'X-Bar' => 'Foo', 'X-Foo' => 'X-Bar'];

        $this->assertEquals($expected, $actual);
    }

    public function optionsDataProvider(): array
    {
        return [
            'timeout' => [
                function (XttpPending $pending) {
                    return $pending->timeout(10);
                }, 'timeout', 10,
            ],
            'withDigestAuth' => [
                function (XttpPending $pending) {
                    return $pending->withDigestAuth('Foo', 'Bar');
                }, 'auth', ['Foo', 'Bar', 'digest'],
            ],
            'withBasicAuth' => [
                function (XttpPending $pending) {
                    return $pending->withBasicAuth('Foo', 'Bar');
                }, 'auth', ['Foo', 'Bar'],
            ],
            'withoutRedirecting' => [
                function (XttpPending $pending) {
                    return $pending->withoutRedirecting();
                }, 'allow_redirects', false,
            ],
            'withoutVerifying' => [
                function (XttpPending $pending) {
                    return $pending->withoutVerifying();
                }, 'verify', false,
            ],
        ];
    }

    public function headerDataProvider(): array
    {
        return [
            'accept' => [
                function (XttpPending $pending) {
                    return $pending->accept('application/json');
                }, ['Accept' => 'application/json'],
            ],
            'contentType' => [
                function (XttpPending $pending) {
                    return $pending->contentType('application/json');
                }, ['Content-Type' => 'application/json'],
            ],
        ];
    }

    /**
     * @test
     * @group middleware
     */
    public function add_response_middlewares()
    {
        $middlewares = $this->makeResponseMiddlewares();

        $this->pending->withResponseMiddleware([$middlewares[0], $middlewares[1]]);

        $this->pending->withResponseMiddleware($middlewares[2], true);

        $ordered = [$middlewares[2], $middlewares[0], $middlewares[1]];

        $array = $this->pending->getMiddleware();

        $this->assertEquals($ordered, $array);

        foreach ($ordered as $i => $fn) {
            $this->assertEquals(Middleware::mapResponse($fn), $array[$i]);
        }
    }

    private function makeResponseMiddlewares(): array
    {
        return [
            static function (Psr\Http\Message\ResponseInterface $response, array $options = []) {
                return $response->withHeader('Foo', 'Bar');
            },
            static function (Psr\Http\Message\ResponseInterface $response, array $options = []) {
                return $response->withStatus(500);
            },
            static function (Psr\Http\Message\ResponseInterface $response, array $options = []) {
                return $response->withoutHeader('Foo');
            },
        ];
    }

    /**
     * @test
     * @group middleware
     */
    public function add_request_middlewares()
    {
        $middlewares = $this->makeRequestMiddlewares();

        $this->pending->withRequestMiddleware([$middlewares[0], $middlewares[1]]);

        $this->pending->withRequestMiddleware($middlewares[2], true);

        $ordered = [$middlewares[2], $middlewares[0], $middlewares[1]];

        $array = $this->pending->getMiddleware();

        $this->assertEquals($ordered, $array);

        foreach ($ordered as $i => $fn) {
            $this->assertEquals(Middleware::mapRequest($fn), $array[$i]);
        }
    }

    private function makeRequestMiddlewares(): array
    {
        return [
            static function (Psr\Http\Message\RequestInterface $request, array $options = []) {
                return $request->withHeader('Foo', 'Bar');
            },
            static function (Psr\Http\Message\RequestInterface $request, array $options = []) {
                return $request->withMethod('POST');
            },
            static function (Psr\Http\Message\RequestInterface $request, array $options = []) {
                return $request->withoutHeader('Foo');
            },
        ];
    }

    /**
     * @test
     * @group middleware
     */
    public function singleMiddleware_adds_middlewares()
    {
        $responseMiddle = static function (Psr\Http\Message\ResponseInterface $response, array $options) {
        };

        $requestMiddle = static function (Psr\Http\Message\RequestInterface $response, array $options) {
        };

        $otherMiddle = static function () {
        };

        $this->pending->withResponseMiddleware($responseMiddle);

        $this->pending->withRequestMiddleware($responseMiddle);

        $this->pending->withMiddleware($otherMiddle);

        $middlewares = $this->pending->getMiddleware();

        $this->assertEquals(Middleware::mapResponse($responseMiddle), $middlewares[0]);

        $this->assertEquals(Middleware::mapRequest($requestMiddle), $middlewares[1]);

        $this->assertEquals($otherMiddle, $middlewares[2]);
    }

    /**
     * @test
     * @group cookies
     */
    public function can_add_and_set_cookies()
    {
        $url = 'https://johnathansmith.com';
        $one = ['Johnathan' => 'Smith', 'Sarah' => 'Smith'];
        $two = ['Kristabelle' => 'Levi', 'Johnathan' => 'Ray'];

        $this->pending->setUrl($url);
        $this->pending->withCookies($one);
        $this->pending->withCookies($two);

        $xttpCookie = $this->pending->getXttpCookie();

        $names = $xttpCookie->getNames();
        $values = $xttpCookie->getValues();

        $this->assertEquals(['Kristabelle', 'Johnathan', 'Sarah'], $names);
        $this->assertEquals(['Levi', 'Ray', 'Smith'], $values);

        $cookies = $this->pending->getCookiesArray();
        $names = array_column($cookies, 'Name');
        $values = array_column($cookies, 'Value');
        $this->assertEquals(['Kristabelle', 'Johnathan', 'Sarah'], $names);
        $this->assertEquals(['Levi', 'Ray', 'Smith'], $values);
    }

    /**
     * Needs to be combined for easier testing.
     * @test
     * @group middleware
     */
    public function with_history_and_mock_and_retry_middleware()
    {
        $body1 = 'Hello, World';
        $body2 = 'Hello, World Again';

        $mockHandler = new MockHandler([
            new Response(500, ['X-Foo' => 'Bar'], $body1),
            new Response(200, ['X-Foo' => 'Baz'], $body2),
        ]);

        $container = [];

        $this->pending
            ->withMock($mockHandler)
            ->withRetryMiddleware(TestHelpers::retryDecider(), TestHelpers::retryDelay())
            ->withHistory($container);

        $this->pending->getClient()->get('/');

        $this->assertCount(2, $container);

        $this->assertEquals(500, $container[0]['response']->getStatusCode());

        $this->assertEquals($body1, (string) $container[0]['response']->getBody());

        $this->assertEquals('Bar', $container[0]['response']->getHeader('X-Foo')[0]);

        $this->assertEquals(200, $container[1]['response']->getStatusCode());

        $this->assertEquals($body2, (string) $container[1]['response']->getBody());

        $this->assertEquals('Baz', $container[1]['response']->getHeader('X-Foo')[0]);
    }
}
