<?php

namespace Demo;

interface HsmSubscriptions
{
    public function persist(HsmSubscription $subscription): void;

    public function contains($accountId, $service, $serviceId): bool;
}
