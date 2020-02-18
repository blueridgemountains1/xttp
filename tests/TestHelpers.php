<?php

namespace JohnathanSmith\Xttp\Tests;

use Closure;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class TestHelpers
{
    public static function retryDecider(): Closure
    {
        return static function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) {
            // Limit the number of retries to 5
            if ($retries >= 5) {
                return false;
            }

            // Retry connection exceptions
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // Retry on server errors
                if ($response->getStatusCode() >= 500) {
                    return true;
                }
            }

            return false;
        };
    }

    public static function retryDelay(): Closure
    {
        return static function ($numberOfRetries) {
            return 0;
        };
    }
}
