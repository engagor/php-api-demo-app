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

    public function webhooks(Request $request, Response $response): Response
    {
        $rawMention = $request->getBody()->getContents();

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
        $authorName = $mention['author']['name'];

        $phoneNumber = preg_replace('/^.*?(\+?[\d\s\(\)\.\-\/]+).*?/', '$1', strip_tags($messageContent));
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

        try {
            $numberProto = $phoneUtil->parse($phoneNumber, 'BE');
            $phoneNumber = $phoneUtil->format(
                $numberProto,
                \libphonenumber\PhoneNumberFormat::E164
            );
        } catch (\libphonenumber\NumberParseException $e) {
            $phoneNumber = '';
        }

        $containsYes = mb_stripos(strip_tags($messageContent), 'yes') !== false;

        if ($this->hsmSubscriptions->contains($accountId, $service, $authorId)) {
            return $response;
        }

        $contactedAt = $this->alreadyContactedContacts->whenDidWeContact($accountId, $service, $authorId);
        $twoDaysAgo = new DateTimeImmutable('2 days ago');
        if ($containsYes === false && $contactedAt !== null && $contactedAt > $twoDaysAgo) {
            return $response;
        }

        $replyMessage = "Hi {$authorName}, an agent will help you soon. ";
        $replyMessage .= 'In the mean time, can we have your consent and phone number to contact via WhatsApp in case of trouble? ';
        $replyMessage .= "Reply with your phone number and the word 'YES' if you want this.";

        if ($containsYes === true && !empty($phoneNumber)) {
            $subscription = new HsmSubscription(
                $accountId,
                $service,
                $authorId,
                $authorName,
                $phoneNumber
            );
            $this->hsmSubscriptions->persist($subscription);

            $replyMessage = "Thanks! We'll keep you posted.";
        } elseif ($containsYes === true && empty($phoneNumber)) {
            $replyMessage = "We didn't quite get your phone number. ";
            $replyMessage .= 'Your reply should look like this: ';
            $replyMessage .= 'YES +32 486 00 00 00';
        } else {
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
            [],
            '',
            $replyMessage,
            'queued',
            null,
            $topicId,
            $id
        );

        return $response;
    }
}
