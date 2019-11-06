<?php

namespace Demo;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ClientRouteToStaging implements ClientInterface
{
    private $client;
    private $stagingDomain;

    public function __construct(ClientInterface $client, $stagingDomain)
    {
        $this->client = $client;
        $this->stagingDomain = $stagingDomain;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();
        $uri = $uri->withHost($this->stagingDomain);
        $request = $request->withUri($uri);

        return $this->client->sendRequest($request);
    }
}
