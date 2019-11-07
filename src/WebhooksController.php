<?php

namespace Demo;

use DateTimeImmutable;
use Engagor\Client;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class WebhooksController
{
    private $client;
    private $hsmSubscriptions;
    private $alreadyContactedContacts;

    public function __construct(
        Client $client,
        HsmSubscriptions $hsmSubscriptions,
        AlreadyContacted $alreadyContactedContacts
    ) {
        $this->client = $client;
        $this->hsmSubscriptions = $hsmSubscriptions;
        $this->alreadyContactedContacts = $alreadyContactedContacts;
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
        $messageContent = $mention['message']['content'];
        $messageType = $mention['message']['type'];
        $service = $mention['source']['service'];
        $accountId = preg_replace('/^.*messages\/(\d+)\/.*$/', '$1', $permalink);
        $profileId = preg_replace('/^.*(?:twitter|facebook)\.com\/(\d+)\/.*$/', '$1', $mention['source']['url']);
        $authorId = $mention['author']['id'];

        $phoneNumber = preg_replace('/^.*?(\+?[\d\s\(\)\.\-\/]+).*?/', '$1', strip_tags($messageContent));
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

        try {
            $numberProto = $phoneUtil->parse($phoneNumber, 'BE');
            $phoneNumber = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
        } catch (\libphonenumber\NumberParseException $e) {
            $phoneNumber = '';
        }

        $containsYes = mb_stripos(strip_tags($messageContent), 'yes') !== false;

        if ($this->hsmSubscriptions->contains($accountId, $service, $authorId)) {
            return $response;
        }

        $contactedAt = $this->alreadyContactedContacts->whenDidWeContact($accountId, $service, $authorId);
        error_log(print_r($contactedAt, true));
        $twoDaysAgo = new DateTimeImmutable('2 days ago');
        if ($containsYes === false && $contactedAt !== null && $contactedAt > $twoDaysAgo) {
            error_log(print_r('already contacted', true));

            return $response;
        }

        $replyMessage = "Hi {$mention['author']['name']}, an agent will help you soon. ";
        $replyMessage .= 'In the mean time, can we have your consent and phone number to contact via WhatsApp in case of trouble? ';
        $replyMessage .= "Reply with your phone number and the word 'YES' if you want this.";

        if ($containsYes === true && !empty($phoneNumber)) {
            $subscription = new HsmSubscription($accountId, $service, $authorId, $mention['author']['name'], $phoneNumber);
            $this->hsmSubscriptions->persist($subscription);

            $replyMessage = "Thanks! We'll keep you posted.";
        } elseif ($containsYes === true && empty($phoneNumber)) {
            $replyMessage = "We didn't quite get your phone number. ";
            $replyMessage .= 'Your reply should look like this: ';
            $replyMessage .= 'YES +32 486 00 00 00';
        } else {
            error_log('yoo ben');
            $this->alreadyContactedContacts->weJustContacted($accountId, $service, $authorId);
        }

        $result = $this->client->publish(
            $accountId,
            'privatemessage',
            [
                [
                    'type' => $service,
                    'service_id' => $profileId,
                ],
            ],
            [
                $authorId,
            ],
            'Move along',
            $replyMessage,
            'queued',
            null,
            $topicId,
            $id
        );

        error_log(print_r($result, true));

        return $response;
    }
}
