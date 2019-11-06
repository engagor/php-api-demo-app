<?php

use DI\Bridge\Slim\Bridge;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__ . '/../');
$dotenv->load();

$container = new Container();
$container->set('http-factory', function () {
    return new Nyholm\Psr7\Factory\Psr17Factory();
});
$container->set(Psr\Http\Client\ClientInterface::class, function (Container $container) {
    return new Buzz\Client\Curl($container->get('http-factory'));
});
$container->set(Engagor\Authentication::class, function (Container $container) {
    $httpRequestFactory = $container->get(Psr\Http\Client\ClientInterface::class);
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
$container->set(Engagor\Client::class, function (Container $container) {
    if (file_exists(__DIR__ . '/../token') === false) {
        throw new RuntimeException('No tokens file, please authenticate first.');
    }

    $tokens = unserialize(file_get_contents(__DIR__ . '/../token'));

    return new Engagor\Client(
        new Demo\ClientRouteToStaging($container->get(Psr\Http\Client\ClientInterface::class), 'api.toon.neo.engagor.com'),
        $container->get('http-factory'),
        $tokens
    );
});

$app = Bridge::create($container);

$app->get('/', [Demo\TestController::class, 'hello']);
$app->get('/step1', [Demo\OAuthController::class, 'step1']);
$app->get('/step2', [Demo\OAuthController::class, 'step2']);
$app->post('/webhooks', [Demo\WebhooksController::class, 'webhooks']);

$app->run();
