@extends('layouts.app')

@section('title', 'Notifications — ' . config('app.name'))

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900">Notifications</h1>
@if ($notifications->getCollection()->whereNull('read_at')->isNotEmpty())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="mt-8 space-y-3">
        @forelse ($notifications as $notification)
            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                @csrf
                <button type="submit" class="block w-full rounded-xl border p-5 text-left shadow-sm transition hover:border-brand-200 hover:shadow-md {{ $notification->isRead() ? 'border-slate-200 bg-white' : 'border-brand-200 bg-brand-50/40' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            @unless ($notification->isRead())
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-brand-500"></span>
                            @else
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-transparent"></span>
                            @endunless
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $notification->title }}</p>
                                <p class="mt-0.5 text-sm text-slate-600">{{ $notification->body }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </button>
            </form>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="font-display text-lg font-bold text-slate-900">You’re all caught up</p>
                <p class="mt-2 text-sm text-slate-500">Notifications about your bookings and activity will appear here.</p>
            </div>
       @endforelse
    </div>

    {{ $notifications->links() }}
</section>
@endsection