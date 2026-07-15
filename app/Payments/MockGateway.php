<?php

namespace App\Payments;

use App\Models\Payment;
use Illuminate\Support\Str;

class MockGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'mock';
    }

    public function label(): string
    {
        return config('payments.gateways.mock.label', 'Test payment (sandbox)');
    }

    public function isAvailable(): bool
    {
        return true;
    }

    /** Always succeeds synchronously — for local development. */
    public function charge(Payment $payment): PaymentResult
    {
        return PaymentResult::completed('MOCK-' . strtoupper(Str::random(10)));
    }

    /** Synchronous gateway — never receives a return/webhook callback. */
    public function verify(array $responseFields): bool
    {
        return true;
    }
}