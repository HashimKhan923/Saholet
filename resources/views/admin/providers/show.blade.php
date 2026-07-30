@extends('layouts.admin')

@section('title', 'Review provider — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('admin.providers.index') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400">&larr; Provider approvals</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $provider->user->name }}</h1>
        @switch($provider->status)
            @case('approved')
                <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-950/40 dark:text-brand-400">Approved</span>
                @break
            @case('pending')
                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">Pending review</span>
                @break
            @case('rejected')
                <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-400">Rejected</span>
                @break
            @default
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Draft</span>
        @endswitch
    </div>

    {{-- ═══ Earnings & activity strip ═══ --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Lifetime earned</p>
            <p class="mt-1.5 font-display text-lg font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($earnings['lifetime_earned'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Available balance</p>
            <p class="mt-1.5 font-display text-lg font-extrabold text-brand-700 dark:text-brand-400">Rs. {{ number_format($earnings['available_balance'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">In escrow</p>
            <p class="mt-1.5 font-display text-lg font-extrabold text-amber-600 dark:text-amber-400">Rs. {{ number_format($earnings['escrow_balance'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Withdrawn</p>
            <p class="mt-1.5 font-display text-lg font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($earnings['total_withdrawn'], 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Bookings</p>
            <p class="mt-1.5 font-display text-lg font-extrabold text-slate-900 dark:text-white">{{ $bookingCounts['total'] }}</p>
            <p class="text-[11px] text-slate-400">{{ $bookingCounts['completed'] }} completed</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Rating</p>
            <p class="mt-1.5 font-display text-lg font-extrabold text-slate-900 dark:text-white">
                {{ $provider->reviews_count > 0 ? number_format((float) $provider->rating_avg, 1) : '—' }}
            </p>
            <p class="text-[11px] text-slate-400">{{ $provider->reviews_count }} review{{ $provider->reviews_count === 1 ? '' : 's' }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Details</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500 dark:text-slate-400">Email</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->user->email }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Phone</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->user->phone ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Business</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->business_name ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">City</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->city ?: '—' }}</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">Experience</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->experience_years }} yr</dd></div>
                    <div><dt class="text-slate-500 dark:text-slate-400">CNIC</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->cnic_number ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">Address</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->address ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500 dark:text-slate-400">About</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->bio ?: '—' }}</dd></div>
                </dl>
            </div>

            {{-- Payout details --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Payout details</h2>
                @if (! $provider->hasPayoutMethod())
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">This provider hasn't set up a payout method yet.</p>
                @else
                    <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Method</dt>
                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                @switch($provider->payout_method)
                                    @case('bank')
                                        Bank transfer
                                        @break
                                    @case('jazzcash')
                                        JazzCash
                                        @break
                                    @case('easypaisa')
                                        EasyPaisa
                                        @break
                                    @default
                                        {{ ucfirst((string) $provider->payout_method) }}
                                @endswitch
                            </dd>
                        </div>
                        @if ($provider->payout_method === 'bank')
                            <div><dt class="text-slate-500 dark:text-slate-400">Bank name</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->payout_bank_name ?: '—' }}</dd></div>
                        @endif
                        <div><dt class="text-slate-500 dark:text-slate-400">Account title</dt><dd class="font-medium text-slate-800 dark:text-slate-200">{{ $provider->payout_account_title ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">{{ $provider->payout_method === 'bank' ? 'Account / IBAN number' : 'Mobile account number' }}</dt><dd class="font-mono font-medium text-slate-800 dark:text-slate-200">{{ $provider->payout_account_number ?: '—' }}</dd></div>
                    </dl>
                @endif
            </div>

            {{-- Services offered --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Services offered</h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $provider->providerServices->count() }}</span>
                </div>
                @if ($provider->providerServices->isEmpty())
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">This provider hasn't listed any services yet.</p>
                @else
                    <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($provider->providerServices as $offering)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $offering->service->name ?? '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ $offering->service->category->name ?? '—' }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Rs. {{ number_format((float) $offering->price, 0) }}</p>
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $offering->is_active ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400' : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500' }}">
                                        {{ $offering->is_active ? 'Active' : 'Paused' }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Recent bookings --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Recent bookings</h2>
                    <p class="text-xs text-slate-400">{{ $bookingCounts['active'] }} active · {{ $bookingCounts['completed'] }} completed · {{ $bookingCounts['cancelled'] }} cancelled</p>
                </div>
                @if ($bookings->isEmpty())
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No bookings yet.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                            <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="py-2 pr-3">Service</th>
                                    <th class="py-2 pr-3">Customer</th>
                                    <th class="py-2 pr-3">Date</th>
                                    <th class="py-2 pr-3">Price</th>
                                    <th class="py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($bookings as $booking)
                                    <tr>
                                        <td class="py-2.5 pr-3 font-medium text-slate-800 dark:text-slate-200">{{ $booking->service->name ?? '—' }}</td>
                                        <td class="py-2.5 pr-3 text-slate-600 dark:text-slate-400">{{ $booking->consumer->name ?? '—' }}</td>
                                        <td class="py-2.5 pr-3 text-slate-500 dark:text-slate-400">{{ $booking->scheduled_date?->format('d M Y') ?? '—' }}</td>
                                        <td class="py-2.5 pr-3 text-slate-600 dark:text-slate-400">Rs. {{ number_format((float) $booking->price, 0) }}</td>
                                        <td class="py-2.5">
                                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ str_replace('_', ' ', $booking->status) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Reviews --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Reviews</h2>
                    @if ($provider->reviews_count > 0)
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ number_format((float) $provider->rating_avg, 1) }} ★ · {{ $provider->reviews_count }}</span>
                    @endif
                </div>
                @if ($provider->reviews->isEmpty())
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No reviews yet.</p>
                @else
                    <ul class="mt-4 space-y-4">
                        @foreach ($provider->reviews as $review)
                            <li class="border-b border-slate-100 pb-4 last:border-0 last:pb-0 dark:border-slate-800">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $review->consumer->name ?? '—' }}</p>
                                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                </div>
                                @if ($review->comment)
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $review->comment }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-400">{{ $review->created_at->format('d M Y') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Portfolio --}}
            @if ($provider->portfolioPhotos->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Portfolio</h2>
                    <div class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-4">
                        @foreach ($provider->portfolioPhotos as $photo)
                            <a href="{{ $photo->url() }}" target="_blank" rel="noopener" class="group relative aspect-square overflow-hidden rounded-xl">
                                <img src="{{ $photo->url() }}" alt="{{ $photo->caption }}" class="h-full w-full object-cover transition group-hover:scale-105">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Documents --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Documents</h2>
                @if ($provider->documents->isEmpty())
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No documents uploaded.</p>
                @else
                    <ul class="mt-4 space-y-2">
                        @foreach ($provider->documents as $doc)
                            <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5 text-sm dark:bg-slate-800">
                                <div>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $doc->label() }}</span>
                                    <span class="ml-2 text-xs text-slate-400 dark:text-slate-500">{{ $doc->original_name }}</span>
                                </div>
                                <a href="{{ route('provider-documents.show', $doc) }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">View</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($provider->isRejected() && $provider->rejection_reason)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-950/40">
                    <h2 class="font-display text-sm font-bold text-red-900 dark:text-red-300">Rejection reason</h2>
                    <p class="mt-1 text-sm text-red-800 dark:text-red-400">{{ $provider->rejection_reason }}</p>
                </div>
            @endif
        </div>

        {{-- Decision --}}
        <aside class="lg:col-span-1 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Decision</h2>

                @if ($provider->isPending())
                    <form method="POST" action="{{ route('admin.providers.approve', $provider) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Approve</button>
                    </form>

                    <form method="POST" action="{{ route('admin.providers.reject', $provider) }}" class="mt-4 space-y-3">
                        @csrf
                        <label for="rejection_reason" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Reason for rejection</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                            class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('rejection_reason') }}</textarea>
                        <button type="submit" class="w-full rounded-lg border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40">Reject</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        @if ($provider->isApproved())
                            Approved{{ $provider->reviewed_at ? ' on ' . $provider->reviewed_at->format('d M Y') : '' }}{{ $provider->reviewer ? ' by ' . $provider->reviewer->name : '' }}.
                        @elseif ($provider->isRejected())
                            Rejected{{ $provider->reviewed_at ? ' on ' . $provider->reviewed_at->format('d M Y') : '' }}. The provider can update and resubmit.
                        @else
                            This application hasn’t been submitted yet.
                        @endif
                    </p>
                @endif
            </div>

            {{-- Withdrawal history --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white">Withdrawal requests</h2>
                @if ($provider->withdrawalRequests->isEmpty())
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">No withdrawal requests yet.</p>
                @else
                    <ul class="mt-4 space-y-3">
                        @foreach ($provider->withdrawalRequests as $withdrawal)
                            <li class="flex items-center justify-between gap-2 text-sm">
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">Rs. {{ number_format((float) $withdrawal->amount, 0) }}</p>
                                    <p class="text-xs text-slate-400">{{ $withdrawal->created_at->format('d M Y') }}</p>
                                </div>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide
                                    {{ match($withdrawal->status) {
                                        'paid' => 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400',
                                        'rejected' => 'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400',
                                        default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                                    } }}">
                                    {{ $withdrawal->status }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </aside>
    </div>
</section>
@endsection
