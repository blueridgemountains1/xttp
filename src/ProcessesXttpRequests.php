<?php

namespace JohnathanSmith\Xttp;

use GuzzleHttp\ClientInterface;

interface ProcessesXttpRequests
{
    public function process(MakesXttpPending $xttpPending, ClientInterface $client);

    public function makeOptions(MakesXttpPending $xttpPending): array;
}
