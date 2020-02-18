<?php

namespace JohnathanSmith\Xttp;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

interface MakesXttpPending
{
    public function asJson();

    public function contentType(string $contentType);

    public function getMethod(): string;

    public function setMethod(string $method);

    public function getUrl(): string;

    public function setUrl(string $url);

    public function getOptions(): array;

    public function setOptions(iterable $options);

    public function getOption(string $key);

    public function addOption(iterable $option);

    public function addHeader(iterable $header);

    /**
     * @param  \JohnathanSmith\Xttp\ProcessesXttpRequests|null|\JohnathanSmith\Xttp\XttpProcessor  $processesXttpRequests
     * @param  \GuzzleHttp\ClientInterface|null|Client  $client
     *
     * @return \JohnathanSmith\Xttp\XttpResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null
    ): XttpResponse;

    /**
     * @param  iterable|Closure  $middlewares
     * @param  bool  $prepend
     */
    public function withRequestMiddleware($middlewares, $prepend = false);

    /**
     * @return Closure[]
     */
    public function getMiddleware(): array;

    /**
     * @param  iterable|Closure  $middlewares
     * @param  bool  $prepend
     */
    public function withResponseMiddleware($middlewares, $prepend = false);

    /**
     * @param  Closure|Closure[]  $middlewares
     * @param  bool  $prepend
     */
    public function withMiddleware($middlewares, $prepend = false);

    public function withRetryMiddleware(Closure $decider, Closure $delay);

    public function singleMiddleware(Closure $middleware, string $type = null, $prepend = false): void;

    public function clearMiddleware();

    public function withoutRedirecting();

    public function withoutVerifying();

    public function asMultipart();

    public function getContentType(): string;

    public function getHeaders(): array;

    public function setHeaders(iterable $headers);

    public function asFormParams();

    public function accept(string $header);

    public function withHeaders(iterable $headers);

    public function withBasicAuth($username, $password);

    public function withDigestAuth($username, $password);

    public function withCookies(iterable $cookies, string $domain = '/');

    public function timeout(int $seconds);

    public function getBodyFormat(): string;

    public function setBodyFormat(string $bodyFormat = 'json');

    public function getXttpCookie(): HandlesXttpCookies;

    public function getCookiesArray(): array;

    public function withMock(MockHandler $mockHandler);

    public function withHistory(array &$container);

    public function withStack(HandlerStack $handlerStack);

    public function makeStack();

    public function getStack(): HandlerStack;

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(): ClientInterface;

    public static function new();
}
