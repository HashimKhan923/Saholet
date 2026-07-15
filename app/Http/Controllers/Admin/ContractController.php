<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Contract;
use App\Models\ContractItem;
use App\Models\ContractMilestone;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'submitted');

        $validStatuses = ['submitted', 'quoted', 'accepted', 'in_progress', 'completed', 'rejected', 'cancelled', 'all'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'submitted';
        }

        $query = Contract::with('consumer:id,name')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $contracts = $query->paginate(15)->withQueryString();

        return view('admin.contracts.index', compact('contracts', 'status'));
    }

    public function show(Contract $contract): View
    {
        $contract->load([
            'consumer:id,name,email,phone',
            'items.service.category',
            'items.providerProfile.user',
            'items.booking',
            'photos',
            'milestones.payment',
        ]);

        $eligibleProviders = [];
        foreach ($contract->items as $item) {
            if ($item->isAssignable()) {
                $eligibleProviders[$item->id] = ProviderProfile::approved()
                    ->whereHas('providerServices', fn ($q) => $q->where('service_id', $item->service_id)->where('is_active', true))
                    ->with('user:id,name')
                    ->get();
            }
        }

        return view('admin.contracts.show', compact('contract', 'eligibleProviders'));
    }

    public function quote(Request $request, Contract $contract): RedirectResponse
    {
        if (! $contract->isSubmitted()) {
            return back()->with('error', 'Only submitted contracts can be quoted.');
        }

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array'],
            'items.*.price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'milestones' => ['required', 'array', 'min:1'],
            'milestones.*.title' => ['required', 'string', 'max:255'],
            'milestones.*.amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
        ]);

        $contract->load('items');

        foreach ($data['items'] as $itemId => $itemData) {
            $item = $contract->items->firstWhere('id', (int) $itemId);
            abort_unless($item, 404);

            $item->update([
                'quoted_price' => $itemData['price'],
                'status' => ContractItem::STATUS_QUOTED,
            ]);
        }

        $total = collect($data['items'])->sum('price');

        DB::transaction(function () use ($contract, $data, $total) {
            foreach ($data['milestones'] as $sequence => $milestone) {
                $contract->milestones()->create([
                    'title' => $milestone['title'],
                    'amount' => $milestone['amount'],
                    'sequence' => $sequence + 1,
                    'status' => ContractMilestone::STATUS_PENDING,
                ]);
            }

            $contract->update([
                'status' => Contract::STATUS_QUOTED,
                'quoted_total' => $total,
                'admin_notes' => $data['admin_notes'] ?? null,
                'quoted_by' => auth()->id(),
                'quoted_at' => now(),
            ]);
        });

        app(Notifier::class)->notify(
            $contract->consumer,
            'contract',
            'Your contract has been quoted',
            $contract->title . ' (' . $contract->reference . ') is ready for your review — total Rs. ' . number_format($total, 0) . '.',
            route('consumer.contracts.show', $contract)
        );

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', 'Quote sent to the consumer.');
    }

    public function assignProvider(Request $request, Contract $contract, ContractItem $item): RedirectResponse
    {
        abort_unless($item->contract_id === $contract->id, 404);

        if (! $contract->isAccepted() && $contract->status !== Contract::STATUS_IN_PROGRESS) {
            return back()->with('error', 'The consumer must accept the contract before providers can be assigned.');
        }

        if (! $item->isAssignable()) {
            return back()->with('error', 'This item is not ready to be assigned.');
        }

        $data = $request->validate([
            'provider_profile_id' => ['required', 'exists:provider_profiles,id'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'date_format:H:i'],
        ]);

        $provider = ProviderProfile::approved()->findOrFail($data['provider_profile_id']);

        $clash = Booking::where('provider_profile_id', $provider->id)
            ->where('scheduled_date', $data['scheduled_date'])
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->get(['scheduled_time'])
            ->contains(fn ($b) => substr($b->scheduled_time, 0, 5) === $data['scheduled_time']);

        if ($clash) {
            return back()->with('error', 'This provider already has a booking at that time. Please choose another slot.');
        }

        $item->load('service', 'contract');

        [$booking, $payment] = DB::transaction(function () use ($item, $contract, $provider, $data) {
            $booking = Booking::create([
                'reference' => $this->generateBookingReference(),
                'consumer_id' => $contract->consumer_id,
                'corporate_account_id' => $contract->corporate_account_id,
                'provider_profile_id' => $provider->id,
                'service_id' => $item->service_id,
                'contract_item_id' => $item->id,
                'scheduled_date' => $data['scheduled_date'],
                'scheduled_time' => $data['scheduled_time'],
                'price' => $item->quoted_price,
                'duration_minutes' => $item->service->duration_minutes,
                'address' => $contract->address,
                'notes' => $item->notes,
                'status' => Booking::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            $payment = Payment::create([
                'reference' => $this->generatePaymentReference(),
                'booking_id' => $booking->id,
                'consumer_id' => $contract->consumer_id,
                'gateway' => 'contract',
                'amount' => $item->quoted_price,
                'status' => Payment::STATUS_ESCROW,
                'paid_at' => now(),
            ]);

            $item->update([
                'status' => ContractItem::STATUS_ASSIGNED,
                'provider_profile_id' => $provider->id,
                'booking_id' => $booking->id,
            ]);

            if ($contract->status === Contract::STATUS_ACCEPTED) {
                $contract->update(['status' => Contract::STATUS_IN_PROGRESS]);
            }

            return [$booking, $payment];
        });

        app(Notifier::class)->notify(
            $provider->user,
            'contract',
            'New contract assignment',
            'You’ve been assigned to ' . $item->service->name . ' for contract ' . $contract->reference . '.',
            route('provider.bookings.show', $booking)
        );

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', 'Provider assigned and booking created.');
    }

    public function releaseMilestone(Contract $contract, ContractMilestone $milestone): RedirectResponse
    {
        abort_unless($milestone->contract_id === $contract->id, 404);

        if (! $milestone->isEscrow()) {
            return back()->with('error', 'Only escrowed milestones can be released.');
        }

        DB::transaction(function () use ($milestone) {
            $milestone->update(['status' => ContractMilestone::STATUS_RELEASED, 'released_at' => now()]);
            $milestone->payment?->update(['status' => Payment::STATUS_RELEASED, 'released_at' => now()]);
        });

        app(Notifier::class)->notify(
            $contract->consumer,
            'contract',
            'Milestone released',
            $milestone->title . ' for ' . $contract->reference . ' has been released.',
            route('consumer.contracts.show', $contract)
        );

        return back()->with('success', 'Milestone released.');
    }

    private function generateBookingReference(): string
    {
        do {
            $ref = 'BK-' . strtoupper(Str::random(6));
        } while (Booking::where('reference', $ref)->exists());

        return $ref;
    }

    private function generatePaymentReference(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(Str::random(8));
        } while (Payment::where('reference', $ref)->exists());

        return $ref;
    }
}
