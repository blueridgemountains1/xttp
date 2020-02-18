<?php

namespace JohnathanSmith\Xttp;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class XttpResponse
{
    /** @var \GuzzleHttp\Cookie\CookieJar */
    public $cookies;

    /** @var \GuzzleHttp\TransferStats */
    public $transferStats;

    /** @var \Psr\Http\Message\ResponseInterface */
    private $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function json(): array
    {
        $json = json_decode((string) $this->response->getBody(), true) ?? [];

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg());
        }

        return $json ?? [];
    }

    public function header(string $header): string
    {
        return $this->response->getHeaderLine($header);
    }

    public function headers(): array
    {
        $h = [];
        foreach ($this->response->getHeaders() as $key => $val) {
            $h[$key] = $val[0];
        }

        return $h;
    }

    public function getEffectiveUri(): \Psr\Http\Message\UriInterface
    {
        return $this->transferStats->getEffectiveUri();
    }

    public function getUri(): string
    {
        return $this->transferStats->getEffectiveUri()->__toString();
    }

    public function getUrl(): string
    {
        return $this->getUri();
    }

    public function isOk(): bool
    {
        return $this->isSuccess();
    }

    public function isSuccess(): bool
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    public function isRedirect(): bool
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    public function isClientError(): bool
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    public function isServerError(): bool
    {
        return $this->status() >= 500;
    }

    public function getCookies(): iterable
    {
        return ! empty($this->cookies) ? $this->cookies->toArray() : [];
    }

    public function getCookie(string $name)
    {
        return $this->cookies->getCookieByName($name);
    }

    public function __toString()
    {
        return $this->body();
    }

    public function body(): string
    {
        return (string) $this->response->getBody();
    }

    public function response(): ResponseInterface
    {
        return $this->response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function __call($method, $args)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $args);
        }

        if (method_exists($this->response, $method)) {
            return $this->response->{$method}(...$args);
        }

        if ($this->response::hasMacro($method)) {
            return $this->macroCall($method, $args);
        }

        throw new InvalidArgumentException("Method {$method} does not exist on Response");
    }
}
