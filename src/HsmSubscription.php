<?php
namespace Demo;

final class HsmSubscription
{
    private $accountId;
    private $service;
    private $serviceId;
    private $name;
    private $phoneNumber;

    public function __construct($accountId, $service, $serviceId, $name, $phoneNumber)
    {
        $this->accountId = $accountId;
        $this->service = $service;
        $this->serviceId = $serviceId;
        $this->name = $name;
        $this->phoneNumber = $phoneNumber;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getService()
    {
        return $this->service;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }
}
