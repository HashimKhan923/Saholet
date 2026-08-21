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

    <div x-show="open" x-cloak x-teleport="body" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/50" x-transition.opacity @click="open = false"></div>
        <div x-show="open" x-transition
            class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-800">
            <button type="button" @click="open = false" class="absolute right-4 top-4 cursor-pointer rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-200" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            <h3 class="pr-6 text-left font-display text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
            <p class="mt-2 text-left text-sm text-slate-600 dark:text-slate-400">{{ $message }}</p>
            <form method="POST" action="{{ $action }}" @submit="submitting = true">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="open = false"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                        Cancel
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition disabled:cursor-not-allowed disabled:opacity-50 {{ $confirmClass }}">
                        <span x-show="!submitting">{{ $confirmLabel }}</span>
                        <span x-show="submitting" x-cloak>Please wait…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
