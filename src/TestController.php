<?php

namespace Demo;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class TestController
{
    public function hello(Request $request, Response $response)
    {
        $html = 'hello';

        if ($request->getQueryParams()['token-success']) {
            $html .= '<p>token success</p>';
        }

        $html .= '<p><a href="/step1">authenticate</a></p>';

        $response->getBody()->write($html);

        return $response;
    }
}
