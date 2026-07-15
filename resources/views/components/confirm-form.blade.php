@props([
    'action',
    'method' => 'POST',
    'buttonLabel',
    'buttonClass' => 'rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50',
    'title' => 'Are you sure?',
    'message' => '',
    'confirmLabel' => 'Confirm',
    'confirmClass' => 'bg-red-600 hover:bg-red-700',
])

<div x-data="{ open: false, submitting: false }" class="inline-block">
    <button type="button" @click="open = true" class="{{ $buttonClass }}">{{ $buttonLabel }}</button>

    <form method="POST" action="{{ $action }}" x-ref="form" class="hidden">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif
    </form>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/50" x-transition.opacity @click="open = false"></div>
        <div x-show="open" x-transition
            class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-800">
            <h3 class="font-display text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $message }}</p>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="open = false"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                    Never mind
                </button>
                <button type="button" :disabled="submitting" @click="submitting = true; $refs.form.submit()"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition disabled:cursor-not-allowed disabled:opacity-50 {{ $confirmClass }}">
                    <span x-show="!submitting">{{ $confirmLabel }}</span>
                    <span x-show="submitting" x-cloak>Please wait…</span>
                </button>
            </div>
        </div>
    </div>
</div>
