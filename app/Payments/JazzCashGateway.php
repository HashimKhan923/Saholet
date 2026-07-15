<?php

namespace App\Payments;

use App\Models\Payment;
use Illuminate\Support\Carbon;

/**
 * JazzCash "Page Redirection" hosted checkout.
 *
 * Reference: JazzCash Merchant Integration Guide — Page Redirection API.
 * The merchant site POSTs a signed form to JazzCash's hosted page; the
 * customer completes payment there; JazzCash redirects the browser back to
 * pp_ReturnURL with the same fields (plus pp_ResponseCode/pp_SecureHash) for
 * verification. Field names/order are stable across JazzCash's sandbox and
 * production Page Redirection product, but cross-check against your current
 * merchant integration PDF before going live — JazzCash has multiple
 * products (Mobile Wallet, Page Redirection, MPay) with slightly different
 * field sets.
 */
class JazzCashGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'jazzcash';
    }

    public function label(): string
    {
        return config('payments.gateways.jazzcash.label', 'JazzCash');
    }

    public function isAvailable(): bool
    {
        $cfg = config('payments.gateways.jazzcash');

        return (bool) ($cfg['enabled'] ?? false)
            && ! empty($cfg['merchant_id'])
            && ! empty($cfg['password'])
            && ! empty($cfg['integrity_salt']);
    }

    public function charge(Payment $payment): PaymentResult
    {
        if (! $this->isAvailable()) {
            return PaymentResult::failed(
                'JazzCash is not configured yet. Set JAZZCASH_* credentials and JAZZCASH_ENABLED=true to enable it.'
            );
        }

        $cfg = config('payments.gateways.jazzcash');
        $now = Carbon::now();

        $fields = [
            'pp_Version' => '1.1',
            'pp_TxnType' => 'MWALLET',
            'pp_Language' => 'EN',
            'pp_MerchantID' => $cfg['merchant_id'],
            'pp_SubMerchantID' => '',
            'pp_Password' => $cfg['password'],
            'pp_BankID' => '',
            'pp_ProductID' => '',
            'pp_TxnRefNo' => $payment->reference,
            // JazzCash amounts are in paisas (PKR × 100), no decimal point.
            // Charged amount excludes any referral credit already applied to this payment.
            'pp_Amount' => (string) (int) round($payment->chargeAmount() * 100),
            'pp_TxnCurrency' => 'PKR',
            'pp_TxnDateTime' => $now->format('YmdHis'),
            'pp_BillReference' => $payment->reference,
            'pp_Description' => 'Sahoulet payment ' . $payment->reference,
            'pp_TxnExpiryDateTime' => $now->copy()->addDay()->format('YmdHis'),
            'pp_ReturnURL' => route('payments.return', ['gateway' => 'jazzcash']),
            'ppmpf_1' => (string) $payment->id,
            'ppmpf_2' => '',
            'ppmpf_3' => '',
            'ppmpf_4' => '',
            'ppmpf_5' => '',
        ];

        $fields['pp_SecureHash'] = $this->secureHash($fields, $cfg['integrity_salt']);

        $env = config('payments.gateways.jazzcash.env', 'sandbox');
        $url = $env === 'production'
            ? 'https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/'
            : 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/';

        return PaymentResult::redirect($payment->reference, $url, $fields);
    }

    /** Verify an inbound pp_SecureHash on the return/webhook callback. */
    public function verify(array $responseFields): bool
    {
        $cfg = config('payments.gateways.jazzcash');
        $incomingHash = $responseFields['pp_SecureHash'] ?? null;

        if (! $incomingHash) {
            return false;
        }

        $expected = $this->secureHash($responseFields, $cfg['integrity_salt']);

        return hash_equals($expected, $incomingHash);
    }

    /**
     * HMAC-SHA256 over the integrity salt + pipe-joined values of every
     * non-empty pp_/ppmpf_ field, sorted alphabetically by key (excluding
     * pp_SecureHash itself), uppercase hex — per JazzCash's documented
     * Page Redirection hash algorithm.
     *
     * @param array<string, string> $fields
     */
    private function secureHash(array $fields, string $integritySalt): string
    {
        unset($fields['pp_SecureHash']);
        ksort($fields);

        $values = array_filter($fields, fn ($v) => $v !== '' && $v !== null);
        $hashString = $integritySalt . '&' . implode('&', $values);

        return strtoupper(hash_hmac('sha256', $hashString, $integritySalt));
    }
}
