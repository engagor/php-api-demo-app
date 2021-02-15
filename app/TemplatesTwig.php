<?php

namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment;

final class TemplatesTwig implements Templates
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function render($path, array $data): string
    {
        return $this->twig->render($path, $data);
    }

    public function renderResponse(Response $response, string $path, array $data): Response
    {
        $response->getBody()->write($this->render($path, $data));

        return $response;
    }
}
