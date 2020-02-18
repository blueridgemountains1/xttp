<?php

namespace JohnathanSmith\Xttp;

use Psr\Http\Message\ResponseInterface;

interface XttpResponseWrapper
{
    public function json(): array;

    public function header(string $header): string;

    public function headers(): array;

    public function getEffectiveUri(): \Psr\Http\Message\UriInterface;

    public function getUri(): string;

    public function getUrl(): string;

    public function isOk(): bool;

    public function isSuccess(): bool;

    public function status(): int;

    public function isRedirect(): bool;

    public function isClientError(): bool;

    public function isServerError(): bool;

    public function getCookies(): iterable;

    public function getCookie(string $name);

    public function body(): string;

    public function response(): ResponseInterface;

    public function getResponse(): ResponseInterface;
}
