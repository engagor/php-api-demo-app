<?php

namespace Demo;

final class HsmSubscriptionsFile implements HsmSubscriptions
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

    public function persist(HsmSubscription $subscription): void
    {
        $content = file_get_contents($this->file);

        $subscriptions = unserialize($content);
        $subscriptions[] = $subscription;

        file_put_contents($this->file, serialize($subscriptions));
    }

    public function contains($accountId, $service, $serviceId): bool
    {
        $content = file_get_contents($this->file);

        /** @var HsmSubscription[] $subscriptions */
        $subscriptions = unserialize($content);

        foreach ($subscriptions as $subscription) {
            if (
                $subscription->getAccountId() == $accountId
                && $subscription->getService() == $service
                && $subscription->getServiceId() == $serviceId
            ) {
                return true;
            }
        }

        return false;
    }
}
