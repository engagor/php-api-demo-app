<?php

namespace Demo;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class TestController
{
    public function hello(Request $request, Response $response)
    {
        $response->getBody()->write('hello');

        return $response;
    }
}
