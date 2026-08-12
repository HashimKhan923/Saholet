<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractItem;
use App\Models\ContractMilestone;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Payments\PaymentManager;
use App\Services\GeofenceService;
use App\Services\Notifier;
use App\Services\PaymentFinalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function __construct(
        private GeofenceService $geofence,
        private PaymentManager $payments,
        private PaymentFinalizer $finalizer,
    ) {}

    public function index(Request $request): View
    {
        $contracts = Contract::where('consumer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('consumer.contracts.index', compact('contracts'));
    }

    public function create(Request $request): View
    {
        $services = Service::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('consumer.contracts.create', compact('services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'preferred_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city' => ['required', 'string', 'max:120'],
            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', 'exists:services,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! $this->geofence->isAllowed($data['latitude'] ?? null, $data['longitude'] ?? null)) {
            return back()->withInput()->with('error', 'Sorry, we’re not serving that city yet.');
        }

        $contract = DB::transaction(function () use ($request, $data) {
            $contract = Contract::create([
                'reference' => $this->generateReference(),
                'consumer_id' => $request->user()->id,
                'corporate_account_id' => $request->user()->corporate_account_id,
                'title' => $data['title'],
                'description' => $data['description'],
                'address' => $data['address'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'city' => $data['city'],
                'preferred_start_date' => $data['preferred_start_date'] ?? null,
                'status' => Contract::STATUS_SUBMITTED,
            ]);

            foreach ($data['items'] as $item) {
                $contract->items()->create([
                    'service_id' => $item['service_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                    'status' => ContractItem::STATUS_PENDING,
                ]);
            }

            foreach ($request->file('photos', []) as $photo) {
                $path = $photo->store("contract-photos/{$contract->id}", 'public');

                $contract->photos()->create([
                    'path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getClientMimeType(),
                    'size' => $photo->getSize(),
                ]);
            }

            return $contract;
        });

        app(Notifier::class)->notifyAdmins(
            'contract',
            'New contract request',
            $contract->title . ' (' . $contract->reference . ') was submitted for a quote.',
            route('admin.contracts.show', $contract)
        );

        return redirect()
            ->route('consumer.contracts.show', $contract)
            ->with('success', 'Contract request submitted. Our team will review and send you a quote.');
    }

    public function show(Request $request, Contract $contract): View
    {
        $this->authorize('view', $contract);

        $contract->load([
            'items.service.category',
            'items.providerProfile.user',
            'items.booking',
            'photos',
            'milestones.payment',
        ]);

        return view('consumer.contracts.show', compact('contract'));
    }

    public function accept(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('accept', $contract);

        if (! $contract->isQuoted()) {
            return back()->with('error', 'This contract is not awaiting your response.');
        }

        $contract->update([
            'status' => Contract::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        app(Notifier::class)->notifyAdmins(
            'contract',
            'Contract accepted',
            $contract->title . ' (' . $contract->reference . ') was accepted by the consumer.',
            route('admin.contracts.show', $contract)
        );

        return back()->with('success', 'Contract accepted. Pay the first milestone to get started.');
    }

    public function reject(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('reject', $contract);

        if (! $contract->isQuoted()) {
            return back()->with('error', 'This contract is not awaiting your response.');
        }

        $contract->update(['status' => Contract::STATUS_REJECTED]);

        app(Notifier::class)->notifyAdmins(
            'contract',
            'Contract rejected',
            $contract->title . ' (' . $contract->reference . ') was rejected by the consumer.',
            route('admin.contracts.show', $contract)
        );

        return back()->with('success', 'Contract rejected.');
    }

    public function cancel(Request $request, Contract $contract): RedirectResponse
    {
        $this->authorize('cancel', $contract);

        if (! $contract->isCancellable()) {
            return back()->with('error', 'This contract can no longer be cancelled.');
        }

        $contract->update([
            'status' => Contract::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Contract cancelled.');
    }

    public function payMilestone(Request $request, Contract $contract, ContractMilestone $milestone): View
    {
        $this->authorize('pay', $contract);
        abort_unless($milestone->contract_id === $contract->id, 404);
        abort_unless($milestone->isPayable(), 404);

        $gateways = collect($this->payments->all())
            ->filter(fn ($g) => config("payments.gateways.{$g->key()}.enabled", false))
            ->values();

        $maxCreditApplicable = min((float) $request->user()->credit_balance, (float) $milestone->amount);
        $companyAccount = config('payments.company_account');

        return view('consumer.contracts.pay-milestone', compact('contract', 'milestone', 'gateways', 'maxCreditApplicable', 'companyAccount'));
    }

    public function storeMilestonePayment(Request $request, Contract $contract, ContractMilestone $milestone): RedirectResponse|View
    {
        $this->authorize('pay', $contract);
        abort_unless($milestone->contract_id === $contract->id, 404);

        if (! $milestone->isPayable()) {
            return redirect()
                ->route('consumer.contracts.show', $contract)
                ->with('error', 'This milestone is not payable.');
        }

        $consumer = $request->user();
        $creditApplied = $request->boolean('apply_credit')
            ? min((float) $consumer->credit_balance, (float) $milestone->amount)
            : 0.0;
        $fullyCoveredByCredit = $creditApplied >= (float) $milestone->amount;

        $available = collect($this->payments->all())
            ->filter(fn ($g) => config("payments.gateways.{$g->key()}.enabled", false))
            ->map->key()
            ->push(Payment::GATEWAY_CASH)
            ->push(Payment::GATEWAY_BANK_TRANSFER)
            ->all();

        $data = $request->validate([
            'gateway' => [$fullyCoveredByCredit ? 'nullable' : 'required', Rule::in($available)],
            'screenshot' => [
                Rule::requiredIf(! $fullyCoveredByCredit && $request->input('gateway') === Payment::GATEWAY_BANK_TRANSFER),
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:8192',
            ],
        ]);

        if (! $fullyCoveredByCredit && in_array($data['gateway'], [Payment::GATEWAY_CASH, Payment::GATEWAY_BANK_TRANSFER], true)) {
            return $this->storeMilestoneCashOrBankTransfer($request, $contract, $milestone, $consumer, $data, $creditApplied);
        }

        if ($fullyCoveredByCredit) {
            $payment = Payment::create([
                'reference' => $this->generatePaymentReference(),
                'contract_milestone_id' => $milestone->id,
                'consumer_id' => $consumer->id,
                'gateway' => 'credit',
                'amount' => $milestone->amount,
                'credit_applied' => $creditApplied,
                'status' => Payment::STATUS_PENDING,
            ]);

            $this->finalizer->finalizeMilestonePayment($payment, 'CREDIT-' . $payment->reference);

            app(Notifier::class)->notifyAdmins(
                'contract',
                'Milestone paid with referral credit',
                $milestone->title . ' for ' . $contract->reference . ' was paid entirely with referral credit.',
                route('admin.contracts.show', $contract)
            );

            return redirect()
                ->route('consumer.contracts.show', $contract)
                ->with('success', 'Milestone paid entirely with your referral credit.');
        }

        $gateway = $this->payments->driver($data['gateway']);

        if (! $gateway->isAvailable()) {
            return back()->with('error', $gateway->label() . ' is not available. Please choose another method.');
        }

        $payment = Payment::create([
            'reference' => $this->generatePaymentReference(),
            'contract_milestone_id' => $milestone->id,
            'consumer_id' => $consumer->id,
            'gateway' => $gateway->key(),
            'amount' => $milestone->amount,
            'credit_applied' => $creditApplied,
            'status' => Payment::STATUS_PENDING,
        ]);

        $result = $gateway->charge($payment);

        if (! $result->success) {
            $payment->update(['status' => Payment::STATUS_FAILED]);

            return back()->with('error', $result->message ?? 'Payment failed.');
        }

        if ($result->status === 'pending') {
            return view('payments.redirect', [
                'redirectUrl' => $result->redirectUrl,
                'redirectFields' => $result->redirectFields,
            ]);
        }

        $this->finalizer->finalizeMilestonePayment($payment, $result->gatewayReference);

        app(Notifier::class)->notifyAdmins(
            'contract',
            'Milestone paid',
            $milestone->title . ' for ' . $contract->reference . ' has been paid into escrow.',
            route('admin.contracts.show', $contract)
        );

        return redirect()
            ->route('consumer.contracts.show', $contract)
            ->with('success', 'Milestone paid and held safely in escrow.');
    }

    /**
     * Cash or bank-transfer-with-screenshot, offered whenever no online gateway
     * is configured. Cash is trusted immediately (no provider is assigned to a
     * milestone yet, so there's no wallet to hold against — same as a real
     * gateway success, {@see PaymentFinalizer::finalizeMilestonePayment()}).
     * Bank transfer stays pending until an admin verifies the screenshot.
     */
    private function storeMilestoneCashOrBankTransfer(
        Request $request,
        Contract $contract,
        ContractMilestone $milestone,
        User $consumer,
        array $data,
        float $creditApplied
    ): RedirectResponse {
        $payment = Payment::create([
            'reference' => $this->generatePaymentReference(),
            'contract_milestone_id' => $milestone->id,
            'consumer_id' => $consumer->id,
            'gateway' => $data['gateway'],
            'amount' => $milestone->amount,
            'credit_applied' => $creditApplied,
            'status' => Payment::STATUS_PENDING,
        ]);

        if ($data['gateway'] === Payment::GATEWAY_CASH) {
            $this->finalizer->finalizeMilestonePayment($payment, 'CASH-' . $payment->reference);

            app(Notifier::class)->notifyAdmins(
                'contract',
                'Milestone paid in cash',
                $milestone->title . ' for ' . $contract->reference . ' was recorded as paid in cash.',
                route('admin.contracts.show', $contract)
            );

            return redirect()
                ->route('consumer.contracts.show', $contract)
                ->with('success', 'Cash payment recorded. Held pending until work progresses.');
        }

        $path = $request->file('screenshot')->store('payment-screenshots', 'public');
        $payment->update(['screenshot_path' => $path]);

        app(Notifier::class)->notifyAdmins(
            'payment',
            'Payment awaiting verification',
            'Bank transfer for ' . $milestone->title . ' (' . $contract->reference . ') needs verification (Rs. ' . number_format((float) $milestone->amount, 0) . ').',
            route('admin.payments.show', $payment)
        );

        return redirect()
            ->route('consumer.contracts.show', $contract)
            ->with('success', "Thanks — we'll confirm your transfer shortly.");
    }

    private function generatePaymentReference(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(Str::random(8));
        } while (Payment::where('reference', $ref)->exists());

        return $ref;
    }

    private function generateReference(): string
    {
        do {
            $ref = 'CTR-' . strtoupper(Str::random(6));
        } while (Contract::where('reference', $ref)->exists());

        return $ref;
    }
}
