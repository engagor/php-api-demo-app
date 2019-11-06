<?php

use DI\Bridge\Slim\Bridge;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set(Demo\TestController::class, function () {
    return new Demo\TestController();
});

$app = Bridge::create($container);

$app->get('/', [Demo\TestController::class, 'hello']);

$app->run();
