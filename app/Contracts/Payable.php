<?php

namespace App\Contracts;

/**
 * Anything a Payment can be tied to that carries its own commission rate —
 * implemented by Booking and Order, so CommissionService/WalletService can
 * hold/release/refund escrow for either through one shared code path instead
 * of duplicating it per purchase type.
 */
interface Payable
{
    /** Commission percent to apply, resolved from whichever rate this payable actually uses. */
    public function commissionRate(): float;

    /** Human label for ledger/notification copy, e.g. "booking BK-XXXXXX" or "order ORD-XXXXXX". */
    public function referenceLabel(): string;
}
