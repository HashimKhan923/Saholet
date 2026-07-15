<?php

namespace App\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /** Machine key, e.g. 'mock'. */
    public function key(): string;

    /** Human label for UI. */
    public function label(): string;

    /** Whether this gateway is ready to use (config present). */
    public function isAvailable(): bool;

    /** Attempt to charge for the given payment record. */
    public function charge(Payment $payment): PaymentResult;

    /**
     * Verify an inbound return/webhook callback's signature. Only meaningful
     * for redirect-based gateways; synchronous gateways (e.g. the mock
     * driver) never receive a callback and may just return true.
     *
     * @param array<string, mixed> $responseFields
     */
    public function verify(array $responseFields): bool;
}