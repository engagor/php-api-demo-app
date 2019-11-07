<?php

use DI\Bridge\Slim\Bridge;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__ . '/../');
$dotenv->load();

$container = require __DIR__ . '/../config/container.php';

$app = Bridge::create($container);

$app->get('/', [Demo\TestController::class, 'hello']);
$app->get('/step1', [Demo\OAuthController::class, 'step1']);
$app->get('/step2', [Demo\OAuthController::class, 'step2']);
$app->post('/webhooks', [Demo\WebhooksController::class, 'webhooks']);

$app->run();
