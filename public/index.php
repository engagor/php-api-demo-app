<?php

use DI\Bridge\Slim\Bridge;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__ . '/../');
$dotenv->load();

$container = new Container();
$container->set(Engagor\Authentication::class, function () {
    $httpRequestFactory = new Nyholm\Psr7\Factory\Psr17Factory();
    $httpClient = new Buzz\Client\Curl($httpRequestFactory);

    $clientId = getenv('CLIENT_ID');
    $clientSecret = getenv('CLIENT_SECRET');

    $authentication = new Engagor\Authentication(
        $httpClient,
        $httpRequestFactory,
        $clientId,
        $clientSecret
    );

    return $authentication;
});

$app = Bridge::create($container);

$app->get('/', [Demo\TestController::class, 'hello']);
$app->get('/step1', [Demo\OAuthController::class, 'step1']);
$app->get('/step2', [Demo\OAuthController::class, 'step2']);
$app->post('/webhooks', [Demo\WebhooksController::class, 'webhooks']);

$app->run();
