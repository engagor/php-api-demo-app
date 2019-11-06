<?php

namespace Demo;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Engagor\Authentication;

final class OAuthController
{
    private $authentication;

    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
    }

    public function step1(Request $request, Response $response)
    {
        $url = $this->authentication->step1(
            [
                'identify',
                'accounts_read',
                'accounts_write',
                'socialprofiles',
                'email',
            ],
            '<RANDOM STATE HERE>'
        );

        $response = $response->withStatus(302);
        $response = $response->withHeader('Location', $url);

        return $response;
    }

    public function step2(Request $request, Response $response)
    {
        $code = $request->getQueryParams()['code'];
        $tokens = $this->authentication->step2($code);

        file_put_contents(__DIR__ . '/../token', serialize($tokens));

        $url = '/?token-success=1';
        $response = $response->withStatus(302);
        $response = $response->withHeader('Location', $url);

        return $response;
    }
}
