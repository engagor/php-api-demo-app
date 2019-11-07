<?php

namespace Demo;

use DateTimeImmutable;

interface AlreadyContacted
{
    public function weJustContacted($accountId, $service, $serviceId): void;

    public function whenDidWeContact($accountId, $service, $serviceId): ?DateTimeImmutable;
}
