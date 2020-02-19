<?php

namespace JohnathanSmith\Xttp;

use GuzzleHttp\ClientInterface;

class Xttp implements HandlesXttp
{
    public static function post(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        MakesXttpPending $pending = null
    ): XttpResponseWrapper {
        return self::makeRequest('POST', $url, $options, $client, $processesXttpRequests, $pending);
    }

    public static function makeRequest(
        string $method,
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        MakesXttpPending $pending = null
    ): XttpResponseWrapper {
        return ($pending ?? XttpPending::new())->setUrl($url)
            ->setMethod($method)
            ->setOptions($options)
            ->send($client, $processesXttpRequests);
    }

    public static function patch(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        MakesXttpPending $pending = null
    ): XttpResponseWrapper {
        return self::makeRequest('PATCH', $url, $options, $client, $processesXttpRequests, $pending);
    }

    public static function put(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        MakesXttpPending $pending = null
    ): XttpResponseWrapper {
        return self::makeRequest('PUT', $url, $options, $client, $processesXttpRequests, $pending);
    }

    public static function delete(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        MakesXttpPending $pending = null
    ): XttpResponseWrapper {
        return self::makeRequest('DELETE', $url, $options, $client, $processesXttpRequests, $pending);
    }

    public static function get(
        string $url,
        array $options = [],
        ClientInterface $client = null,
        ProcessesXttpRequests $processesXttpRequests = null,
        MakesXttpPending $pending = null
    ): XttpResponseWrapper {
        return self::makeRequest('GET', $url, $options, $client, $processesXttpRequests, $pending);
    }
}
