<?php

namespace App\Payments;

use InvalidArgumentException;

class PaymentManager
{
    /** @var array<string, PaymentGateway> */
    protected array $gateways = [];

    /**
     * @param array<int, PaymentGateway> $gateways
     */
    public function __construct(iterable $gateways)
    {
        foreach ($gateways as $gateway) {
            $this->gateways[$gateway->key()] = $gateway;
        }
    }

    public function driver(?string $key = null): PaymentGateway
    {
        $key ??= config('payments.driver', 'mock');

        if (! isset($this->gateways[$key])) {
            throw new InvalidArgumentException("Unknown payment gateway [{$key}].");
        }

        return $this->gateways[$key];
    }

    /** @return array<int, PaymentGateway> */
    public function all(): array
    {
        return array_values($this->gateways);
    }
}