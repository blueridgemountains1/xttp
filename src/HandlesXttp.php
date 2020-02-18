<?php

namespace JohnathanSmith\Xttp;

interface HandlesXttp
{
    public static function post(string $url, array $options = []): XttpResponseWrapper;

    public static function patch(string $url, array $options = []): XttpResponseWrapper;

    public static function put(string $url, array $options = []): XttpResponseWrapper;

    public static function delete(string $url, array $options = []): XttpResponseWrapper;

    public static function get(string $url, array $options = []): XttpResponseWrapper;

    public static function makeRequest(string $method, string $url, array $options = []): XttpResponseWrapper;
}
