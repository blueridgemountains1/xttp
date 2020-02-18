<?php

namespace JohnathanSmith\Xttp;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;

class XttpProcessor implements ProcessesXttpRequests
{
    /** @var array */
    protected $data = [];

    /**
     * @param  \JohnathanSmith\Xttp\MakesXttpPending  $xttpPending
     * @param  \GuzzleHttp\ClientInterface|null|Client  $client
     *
     * @return \JohnathanSmith\Xttp\XttpResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function process(MakesXttpPending $xttpPending, ClientInterface $client)
    {
        $guzzleResponse = $this->makeGuzzleRequest($xttpPending, $client);

        $response = $this->makeXttpResponse($guzzleResponse);

        $this->data = [];

        return $response;
    }

    /**
     * @param  \JohnathanSmith\Xttp\MakesXttpPending  $xttpPending
     * @param  \GuzzleHttp\ClientInterface  $client
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function makeGuzzleRequest(MakesXttpPending $xttpPending, ClientInterface $client): ResponseInterface
    {
        return $client->request(
            $xttpPending->getMethod(),
            $xttpPending->getUrl(),
            $this->makeOptions($xttpPending)
        );
    }

    protected function makeXttpResponse(ResponseInterface $guzzleResponse): XttpResponse
    {
        $response = new XttpResponse($guzzleResponse);

        $response->transferStats = $this->data['transferStats'];

        return $response;
    }

    public function makeOptions(MakesXttpPending $xttpPending): array
    {
        $url = $xttpPending->getUrl();

        $options = $xttpPending->getOptions();

        $query = [];

        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        return array_merge_recursive([
            'query' => $query,
            'on_stats' => function (TransferStats $transferStats) {
                $this->data['transferStats'] = $transferStats;
            },
        ], $options);
    }
}
