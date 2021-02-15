<?php

namespace App;

use Psr\Http\Message\ResponseInterface as Response;

interface Templates
{
    public function render($path, array $data): string;

    public function renderResponse(Response $response, string $path, array $data): Response;
}
