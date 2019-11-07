<?php

use DI\Container;

$container = new Container();
$container->set('http-factory', function () {
    return new Nyholm\Psr7\Factory\Psr17Factory();
});
$container->set(Psr\Http\Client\ClientInterface::class, function (Container $container) {
    return new Buzz\Client\Curl($container->get('http-factory'));
});
$container->set(Engagor\Authentication::class, function (Container $container) {
    $httpClient = $container->get(Psr\Http\Client\ClientInterface::class);
    $httpRequestFactory = $container->get('http-factory');
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
        $container->get(Psr\Http\Client\ClientInterface::class),
        $container->get('http-factory'),
        $tokens
    );
});
$container->set(Demo\HsmSubscriptions::class, function () {
    $file = __DIR__ . '/../subscriptions';

    return new Demo\HsmSubscriptionsFile($file);
});
$container->set(Demo\AlreadyContacted::class, function () {
    $file = __DIR__ . '/../alreadycontacted';

    return new Demo\AlreadyContactedFile($file);
});

return $container;
