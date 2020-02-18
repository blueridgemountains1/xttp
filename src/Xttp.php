<?php

namespace JohnathanSmith\Xttp;

use GuzzleHttp\ClientInterface;
use Illuminate\Support\Traits\Macroable;

class Xttp implements HandlesXttp
{
    use Macroable;

    public static function post(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        XttpPending $pending = null
    ): XttpResponse {
        return self::makeRequest('POST', $url, $options, $client, $processesXttpRequests, $pending);
    }

    public static function makeRequest(
        string $method,
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        XttpPending $pending = null
    ): XttpResponse {

        if (static::hasMacro('req')) {
            return self::req($method, $url, $options, $client, $processesXttpRequests);
        }

        return ($pending ?? XttpPending::new())->setUrl($url)
            ->setMethod($method)
            ->setOptions($options)
            ->process($client, $processesXttpRequests);
    }

    public static function patch(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        XttpPending $pending = null
    ): XttpResponse {
        return self::makeRequest('PATCH', $url, $options, $client, $processesXttpRequests, $pending);
    }

    public static function put(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        XttpPending $pending = null
    ): XttpResponse {
        return self::makeRequest('PUT', $url, $options, $client, $processesXttpRequests, $pending);
    }

    public static function delete(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        XttpPending $pending = null
    ): XttpResponse {
        return self::makeRequest('DELETE', $url, $options, $client, $processesXttpRequests, $pending);
    }

    public static function get(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        XttpPending $pending = null
    ): XttpResponse {
        return self::makeRequest('GET', $url, $options, $client, $processesXttpRequests, $pending);
    }
}
