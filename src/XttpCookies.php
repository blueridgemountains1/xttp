<?php

namespace JohnathanSmith\Xttp;

use GuzzleHttp\Cookie\CookieJar;

class XttpCookies implements HandlesXttpCookies
{
    /** @var \GuzzleHttp\Cookie\CookieJar */
    protected $jar;

    public function __construct(CookieJar $cookieJar)
    {
        $this->jar = $cookieJar;
    }

    public function set(iterable $cookies, string $domain = '/'): void
    {
        $this->jar = CookieJar::fromArray((array) $cookies, $domain);
    }

    public function add(iterable $cookies, string $domain = '/'): void
    {
        $existingCookies = $this->all();
        $newCookies = CookieJar::fromArray((array) $cookies, $domain)->toArray();

        $newNames = array_column($newCookies, 'Name');
        $oldNames = array_column($existingCookies, 'Name');

        foreach (array_diff($oldNames, $newNames) as $key => $name) {
            $newCookies[] = $existingCookies[$key];
        }

        $this->jar = new CookieJar(false, $newCookies);
    }

    public function getJar(): CookieJar
    {
        return $this->jar;
    }

    public function all(): array
    {
        return $this->jar->toArray();
    }

    public function setJar(CookieJar $cookieJar): void
    {
        $this->jar = $cookieJar;
    }

    public function getNames(): array
    {
        return array_column($this->all(), 'Name');
    }

    public function getValues(): array
    {
        return array_column($this->all(), 'Value');
    }
}
