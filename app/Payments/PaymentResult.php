<?php

namespace App\Payments;

class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $status,            // completed | pending | failed
        public ?string $gatewayReference = null,
        public ?string $message = null,
        public ?string $redirectUrl = null,
        /** @var array<string, string> */
        public array $redirectFields = [],
    ) {}

    public static function completed(string $reference): self
    {
        return new self(true, 'completed', $reference, 'Payment completed.');
    }

    /**
     * Hosted-checkout gateways (JazzCash, EasyPaisa) don't confirm synchronously —
     * the browser must be redirected (via an auto-submitting POST form) to the
     * gateway, which later calls back to a return/webhook route.
     *
     * @param array<string, string> $redirectFields
     */
    public static function redirect(string $reference, string $redirectUrl, array $redirectFields, string $message = 'Redirecting to payment gateway…'): self
    {
        return new self(true, 'pending', $reference, $message, $redirectUrl, $redirectFields);
    }

    public static function failed(string $message): self
    {
        return new self(false, 'failed', null, $message);
    }
}