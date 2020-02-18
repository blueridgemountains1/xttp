<?php

namespace JohnathanSmith\Xttp;

use GuzzleHttp\ClientInterface;

interface ProcessesXttpRequests
{
    public function process(XttpPending $xttpPending, ClientInterface $client);

    public function makeOptions(XttpPending $xttpPending): array;
}
