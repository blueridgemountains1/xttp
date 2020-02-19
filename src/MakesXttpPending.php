<?php

namespace JohnathanSmith\Xttp;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

interface MakesXttpPending
{
    public function asJson(): MakesXttpPending;

    public function contentType(string $contentType): MakesXttpPending;

    public function getMethod(): string;

    public function setMethod(string $method): MakesXttpPending;

    public function getUrl(): string;

    public function setUrl(string $url): MakesXttpPending;

    public function getOptions(): array;

    public function setOptions(iterable $options): MakesXttpPending;

    public function getOption(string $key);

    public function addOption(iterable $option): MakesXttpPending;

    public function addHeader(iterable $header): MakesXttpPending;

    /**
     * @param  \JohnathanSmith\Xttp\ProcessesXttpRequests|null|\JohnathanSmith\Xttp\XttpProcessor  $processesXttpRequests
     * @param  \GuzzleHttp\ClientInterface|null|Client  $client
     *
     * @return XttpResponseWrapper
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null
    ): XttpResponseWrapper;

    /**
     * @param  iterable|Closure  $middlewares
     * @param  bool  $prepend
     *
     * @return \JohnathanSmith\Xttp\MakesXttpPending
     */
    public function withRequestMiddleware($middlewares, $prepend = false): MakesXttpPending;

    /**
     * @return Closure[]
     */
    public function getMiddleware(): array;

    /**
     * @param  iterable|Closure  $middlewares
     * @param  bool  $prepend
     *
     * @return \JohnathanSmith\Xttp\MakesXttpPending
     */
    public function withResponseMiddleware($middlewares, $prepend = false): MakesXttpPending;

    /**
     * @param  Closure|Closure[]  $middlewares
     * @param  bool  $prepend
     *
     * @return \JohnathanSmith\Xttp\MakesXttpPending
     */
    public function withMiddleware($middlewares, $prepend = false): MakesXttpPending;

    public function withRetryMiddleware(Closure $decider, Closure $delay): MakesXttpPending;

    public function singleMiddleware(Closure $middleware, string $type = null, $prepend = false): void;

    public function clearMiddleware(): MakesXttpPending;

    public function withoutRedirecting(): MakesXttpPending;

    public function withoutVerifying(): MakesXttpPending;

    public function asMultipart(): MakesXttpPending;

    public function getContentType(): string;

    public function getHeaders(): array;

    public function setHeaders(iterable $headers): MakesXttpPending;

    public function asFormParams(): MakesXttpPending;

    public function accept(string $header): MakesXttpPending;

    public function withHeaders(iterable $headers): MakesXttpPending;

    public function withBasicAuth($username, $password): MakesXttpPending;

    public function withDigestAuth($username, $password): MakesXttpPending;

    public function withCookies(iterable $cookies, string $domain = '/'): MakesXttpPending;

    public function timeout(int $seconds): MakesXttpPending;

    public function getBodyFormat(): string;

    public function setBodyFormat(string $bodyFormat = 'json'): MakesXttpPending;

    public function getXttpCookie(): HandlesXttpCookies;

    public function getCookiesArray(): array;

    public function withMock(MockHandler $mockHandler): MakesXttpPending;

    public function withHistory(array &$container): MakesXttpPending;

    public function withStack(HandlerStack $handlerStack): MakesXttpPending;

    public function makeStack(): MakesXttpPending;

    public function getStack(): HandlerStack;

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(): ClientInterface;

    public static function new(): MakesXttpPending;
}
