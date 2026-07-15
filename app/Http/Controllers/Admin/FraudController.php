<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dispute;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\View\View;

class FraudController extends Controller
{
    private const CANCEL_THRESHOLD = 3;
    private const DISPUTE_THRESHOLD = 2;

    public function index(): View
    {
        // 1) Duplicate phone numbers across accounts
        $dupPhones = User::selectRaw('phone, COUNT(*) c')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');

        $duplicatePhones = [];
        foreach ($dupPhones as $phone) {
            $duplicatePhones[] = [
                'phone' => $phone,
                'users' => User::where('phone', $phone)->get(['id', 'name', 'email', 'role', 'suspended_at']),
            ];
        }

        // 2) Consumers with many self-cancellations
        $cancelRows = Booking::where('status', Booking::STATUS_CANCELLED)
            ->where('cancelled_by', 'consumer')
            ->selectRaw('consumer_id, COUNT(*) c')
            ->groupBy('consumer_id')
            ->havingRaw('COUNT(*) >= ?', [self::CANCEL_THRESHOLD])
            ->pluck('c', 'consumer_id');

        $highCancelConsumers = [];
        if ($cancelRows->isNotEmpty()) {
            $users = User::whereIn('id', $cancelRows->keys())->get()->keyBy('id');
            foreach ($cancelRows as $id => $count) {
                if ($u = $users->get($id)) {
                    $highCancelConsumers[] = ['user' => $u, 'count' => (int) $count];
                }
            }
        }

        // 3) Providers with many disputes
        $disputeRows = Dispute::join('bookings', 'disputes.booking_id', '=', 'bookings.id')
            ->selectRaw('bookings.provider_profile_id ppid, COUNT(*) c')
            ->groupBy('bookings.provider_profile_id')
            ->havingRaw('COUNT(*) >= ?', [self::DISPUTE_THRESHOLD])
            ->pluck('c', 'ppid');

        // 4) Providers with refunds
        $refundRows = Payment::join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->where('payments.status', Payment::STATUS_REFUNDED)
            ->selectRaw('bookings.provider_profile_id ppid, COUNT(*) c, SUM(payments.amount) total')
            ->groupBy('bookings.provider_profile_id')
            ->get()
            ->keyBy('ppid');

        $flaggedProviders = [];
        $ppIds = collect($disputeRows->keys())->merge($refundRows->keys())->unique();
        if ($ppIds->isNotEmpty()) {
            $profiles = ProviderProfile::with('user')->whereIn('id', $ppIds)->get()->keyBy('id');
            foreach ($ppIds as $id) {
                if ($p = $profiles->get($id)) {
                    $flaggedProviders[] = [
                        'profile' => $p,
                        'disputes' => (int) ($disputeRows[$id] ?? 0),
                        'refunds' => (int) ($refundRows[$id]->c ?? 0),
                        'refund_total' => (float) ($refundRows[$id]->total ?? 0),
                    ];
                }
            }
        }

        return view('admin.fraud.index', [
            'duplicatePhones' => $duplicatePhones,
            'highCancelConsumers' => $highCancelConsumers,
            'flaggedProviders' => $flaggedProviders,
            'cancelThreshold' => self::CANCEL_THRESHOLD,
            'disputeThreshold' => self::DISPUTE_THRESHOLD,
        ]);
    }
}