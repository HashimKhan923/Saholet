<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Http\Resources\PaymentResource;
use App\Models\Contract;
use App\Models\ContractItem;
use App\Models\ContractMilestone;
use App\Models\Payment;
use App\Payments\PaymentManager;
use App\Services\GeofenceService;
use App\Services\Notifier;
use App\Services\PaymentFinalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContractController extends Controller
{
    public function __construct(
        private GeofenceService $geofence,
        private PaymentManager $payments,
        private PaymentFinalizer $finalizer,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $contracts = Contract::where('consumer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'contracts' => ContractResource::collection($contracts),
            'pagination' => [
                'current_page' => $contracts->currentPage(),
                'last_page' => $contracts->lastPage(),
                'total' => $contracts->total(),
            ],
        ]);
    }

    /**
     * Submit a multi-service contract request for a manual quote.
     * Body: title, description, preferred_start_date?, address, latitude?, longitude?, city,
     *       items[] ({service_id, quantity, notes?}), photos[]? (max 8 images).
     */
    public function store(Request $request): JsonResponse
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

        if (! $this->geofence->isAllowed($data['city'])) {
            return response()->json(['message' => 'Sorry, we\'re not serving that city yet.'], 422);
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

        $contract->load(['items.service', 'photos']);

        return response()->json(['contract' => new ContractResource($contract)], 201);
    }

    public function show(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        $contract->load([
            'items.service.category',
            'items.providerProfile.user',
            'items.booking',
            'photos',
            'milestones.payment',
        ]);

        return response()->json(['contract' => new ContractResource($contract)]);
    }

    public function accept(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('accept', $contract);

        if (! $contract->isQuoted()) {
            return response()->json(['message' => 'This contract is not awaiting your response.'], 422);
        }

        $contract->update(['status' => Contract::STATUS_ACCEPTED, 'accepted_at' => now()]);

        app(Notifier::class)->notifyAdmins(
            'contract',
            'Contract accepted',
            $contract->title . ' (' . $contract->reference . ') was accepted by the consumer.',
            route('admin.contracts.show', $contract)
        );

        return response()->json(['message' => 'Contract accepted. Pay the first milestone to get started.']);
    }

    public function reject(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('reject', $contract);

        if (! $contract->isQuoted()) {
            return response()->json(['message' => 'This contract is not awaiting your response.'], 422);
        }

        $contract->update(['status' => Contract::STATUS_REJECTED]);

        app(Notifier::class)->notifyAdmins(
            'contract',
            'Contract rejected',
            $contract->title . ' (' . $contract->reference . ') was rejected by the consumer.',
            route('admin.contracts.show', $contract)
        );

        return response()->json(['message' => 'Contract rejected.']);
    }

    public function cancel(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('cancel', $contract);

        if (! $contract->isCancellable()) {
            return response()->json(['message' => 'This contract can no longer be cancelled.'], 422);
        }

        $contract->update(['status' => Contract::STATUS_CANCELLED, 'cancelled_at' => now()]);

        return response()->json(['message' => 'Contract cancelled.']);
    }

    /** Gateways + max credit applicable for a specific milestone, before paying it. */
    public function milestoneOptions(Request $request, Contract $contract, ContractMilestone $milestone): JsonResponse
    {
        $this->authorize('pay', $contract);
        abort_unless($milestone->contract_id === $contract->id, 404);
        abort_unless($milestone->isPayable(), 404);

        $gateways = collect($this->payments->all())
            ->filter(fn ($g) => config("payments.gateways.{$g->key()}.enabled", false) || $g->key() === 'mock')
            ->map(fn ($g) => ['key' => $g->key(), 'label' => $g->label()])
            ->values();

        return response()->json([
            'gateways' => $gateways,
            'max_credit_applicable' => min((float) $request->user()->credit_balance, (float) $milestone->amount),
            'amount' => (float) $milestone->amount,
        ]);
    }

    /** Pay a milestone. Body: gateway (required unless credit fully covers it), apply_credit (bool). */
    public function payMilestone(Request $request, Contract $contract, ContractMilestone $milestone): JsonResponse
    {
        $this->authorize('pay', $contract);
        abort_unless($milestone->contract_id === $contract->id, 404);

        if (! $milestone->isPayable()) {
            return response()->json(['message' => 'This milestone is not payable.'], 422);
        }

        $consumer = $request->user();
        $creditApplied = $request->boolean('apply_credit')
            ? min((float) $consumer->credit_balance, (float) $milestone->amount)
            : 0.0;
        $fullyCoveredByCredit = $creditApplied >= (float) $milestone->amount;

        $available = collect($this->payments->all())
            ->filter(fn ($g) => config("payments.gateways.{$g->key()}.enabled", false) || $g->key() === 'mock')
            ->map->key()
            ->all();

        $data = $request->validate([
            'gateway' => [$fullyCoveredByCredit ? 'nullable' : 'required', Rule::in($available)],
        ]);

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

            return response()->json([
                'message' => 'Milestone paid entirely with your referral credit.',
                'payment' => new PaymentResource($payment->fresh()),
            ], 201);
        }

        $gateway = $this->payments->driver($data['gateway']);

        if (! $gateway->isAvailable()) {
            return response()->json(['message' => $gateway->label() . ' is not available. Please choose another method.'], 422);
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

            return response()->json(['message' => $result->message ?? 'Payment failed.'], 422);
        }

        if ($result->status === 'pending') {
            return response()->json([
                'status' => 'pending',
                'redirect_url' => $result->redirectUrl,
                'redirect_fields' => $result->redirectFields,
            ]);
        }

        $this->finalizer->finalizeMilestonePayment($payment, $result->gatewayReference);

        app(Notifier::class)->notifyAdmins(
            'contract',
            'Milestone paid',
            $milestone->title . ' for ' . $contract->reference . ' has been paid into escrow.',
            route('admin.contracts.show', $contract)
        );

        return response()->json([
            'message' => 'Milestone paid and held safely in escrow.',
            'payment' => new PaymentResource($payment->fresh()),
        ], 201);
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
