<?php

namespace Demo;

use App\Templates;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class TestController
{
    private $templates;

    public function __construct(Templates $templates)
    {
        $this->templates = $templates;
    }

    public function hello(Request $request, Response $response): Response
    {
        $tokenSuccess = isset($request->getQueryParams()['token-success']);

        return $this->templates->renderResponse($response, 'hello.html', ['tokenSuccess' => $tokenSuccess]);
    }
}
