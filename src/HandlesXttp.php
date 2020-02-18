<?php

namespace JohnathanSmith\Xttp;

use GuzzleHttp\ClientInterface;

interface HandlesXttp
{
    public static function post(string $url, array $options = []): XttpResponse;

    public static function patch(string $url, array $options = []): XttpResponse;

    public static function put(string $url, array $options = []): XttpResponse;

    public static function delete(string $url, array $options = []): XttpResponse;

    public static function get(string $url, array $options = []): XttpResponse;

    public static function makeRequest(string $method, string $url, array $options = []): XttpResponse;
}
