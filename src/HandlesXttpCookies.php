<?php

namespace JohnathanSmith\Xttp;

use GuzzleHttp\Cookie\CookieJar;

interface HandlesXttpCookies
{
    public function set(iterable $cookies, string $domain = '/'): void;

    public function add(iterable $cookies, string $domain = '/'): void;

    public function getJar(): CookieJar;

    public function all(): array;

    public function setJar(CookieJar $cookieJar): void;

    public function getNames(): array;

    public function getValues(): array;
}
