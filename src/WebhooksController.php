<?php

namespace Demo;

use Engagor\Client;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class WebhooksController
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function webhooks(Request $request, Response $response)
    {
        $rawMention = $request->getBody()->getContents();

        file_put_contents(
            __DIR__ . '/../logs.txt',
            $rawMention,
            FILE_APPEND
        );

        $mention = json_decode($rawMention, true);
        $topicId = $mention['topic']['id'];
        $id = $mention['id'];
        $permalink = $mention['permalink'];
        $messageType = $mention['message']['type'];
        $service = $mention['source']['service'];
        $accountId = preg_replace('/^.*messages\/(\d+)\/.*$/', '$1', $permalink);
        $profileId = preg_replace('/^.*twitter\.com\/(\d+)\/dm.*$/', '$1', $mention['source']['url']);
        $authorId = $mention['author']['id'];

        $result = $this->client->publish(
            $accountId,
            "privatemessage",
            [
                [
                    'type' => $service,
                    'service_id' => $profileId,
                ]
            ],
            [
                $authorId,
            ],
            'Move along',
            'Hi there!',
            'awaitingapproval',
            null,
            $topicId,
            $id
        );
        error_log(print_r($result, true));

        return $response;
    }
}
