<?php

namespace Demo;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class WebhooksController
{
    public function webhooks(Request $request, Response $response)
    {
        file_put_contents(
            __DIR__ . '/../logs.txt',
            $request->getBody()->getContents(),
            FILE_APPEND
        );

        $response->getBody()->write('hello');

        return $response;
    }
}
