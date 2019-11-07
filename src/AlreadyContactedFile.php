<?php

namespace Demo;

use DateTimeImmutable;

final class AlreadyContactedFile implements AlreadyContacted
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;

        if (!file_exists($this->file)) {
            touch($this->file);
        }

        if (file_get_contents($this->file) == '') {
            file_put_contents($this->file, serialize([]));
        }
    }

    public function weJustContacted($accountId, $service, $serviceId): void
    {
        $content = file_get_contents($this->file);

        $now = new DateTimeImmutable('now');

        $alreadyContacted = unserialize($content);
        $alreadyContacted[] = [
            'accountId' => $accountId,
            'service' => $service,
            'serviceId' => $serviceId,
            'time' => $now->getTimestamp(),
        ];

        file_put_contents($this->file, serialize($alreadyContacted));
    }

    public function whenDidWeContact($accountId, $service, $serviceId): ?DateTimeImmutable
    {
        $content = file_get_contents($this->file);

        $alreadyContactedContacts = unserialize($content);

        foreach ($alreadyContactedContacts as $alreadyContactedContact) {
            if (
                $alreadyContactedContact['accountId'] == $accountId
                && $alreadyContactedContact['service'] == $service
                && $alreadyContactedContact['serviceId'] == $serviceId
            ) {
                return new DateTimeImmutable('@' . $alreadyContactedContact['time']);
            }
        }

        return null;
    }
}
