<?php

namespace App\Payments;

use App\Models\Payment;
use Illuminate\Support\Carbon;

/**
 * EasyPaisa (Telenor Microfinance Bank) hosted checkout.
 *
 * NOTE: EasyPaisa's merchant integration documentation is less consistently
 * public than JazzCash's and has changed field names across product
 * generations ("Open API" vs older direct integrations). The field list and
 * hash construction below follow the commonly-documented Open API hosted
 * checkout shape, but — more so than the JazzCash implementation — this
 * MUST be cross-checked against the merchant integration guide EasyPaisa
 * gives you when you sign up, before relying on it in production.
 */
class EasypaisaGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'easypaisa';
    }

    public function label(): string
    {
        return config('payments.gateways.easypaisa.label', 'Easypaisa');
    }

    public function isAvailable(): bool
    {
        $cfg = config('payments.gateways.easypaisa');

        return (bool) ($cfg['enabled'] ?? false)
            && ! empty($cfg['store_id'])
            && ! empty($cfg['account'])
            && ! empty($cfg['hash_key']);
    }

    public function charge(Payment $payment): PaymentResult
    {
        if (! $this->isAvailable()) {
            return PaymentResult::failed(
                'Easypaisa is not configured yet. Set EASYPAISA_* credentials and EASYPAISA_ENABLED=true to enable it.'
            );
        }

        $cfg = config('payments.gateways.easypaisa');
        $expiry = Carbon::now()->addDay()->format('YmdHis');

        $fields = [
            'storeId' => $cfg['store_id'],
            // Charged amount excludes any referral credit already applied to this payment.
            'amount' => number_format($payment->chargeAmount(), 2, '.', ''),
            'postBackURL' => route('payments.return', ['gateway' => 'easypaisa']),
            'orderRefNum' => $payment->reference,
            'expiryDate' => $expiry,
            'autoRedirect' => '1',
            'paymentMethod' => '',
        ];

        $fields['merchantHashedReq'] = $this->requestHash($fields, $cfg['hash_key']);

        $env = config('payments.gateways.easypaisa.env', 'sandbox');
        $url = $env === 'production'
            ? 'https://easypay.easypaisa.com.pk/easypay/Index.jsf'
            : 'https://easypaystg.easypaisa.com.pk/easypay/Index.jsf';

        return PaymentResult::redirect($payment->reference, $url, $fields);
    }

    /** Verify an inbound response hash on the return/webhook callback. */
    public function verify(array $responseFields): bool
    {
        $cfg = config('payments.gateways.easypaisa');
        $incomingHash = $responseFields['merchantHashedReq'] ?? null;

        if (! $incomingHash) {
            return false;
        }

        $expected = $this->requestHash($responseFields, $cfg['hash_key']);

        return hash_equals($expected, $incomingHash);
    }

    /**
     * HMAC-SHA256 over the pipe-joined values of every field (excluding the
     * hash field itself), sorted alphabetically by key, using the merchant's
     * hash key as the HMAC secret — the same family of construction JazzCash
     * uses. Verify against your EasyPaisa integration guide before going live.
     *
     * @param array<string, string> $fields
     */
    private function requestHash(array $fields, string $hashKey): string
    {
        unset($fields['merchantHashedReq']);
        ksort($fields);

        $values = array_filter($fields, fn ($v) => $v !== '' && $v !== null);

        return strtoupper(hash_hmac('sha256', implode('&', $values), $hashKey));
    }
}
