@props(['index' => [], 'action' => null, 'placeholder' => null])

@php
    $isUrdu = app()->getLocale() === 'ur';
    $placeholder = $placeholder ?? __('messages.landing.search_placeholder');
@endphp

<div class="relative max-w-lg"
     x-data="{
        q: '',
        open: false,
        items: @js($index),
        get matches() {
            const term = this.q.trim().toLowerCase();
            if (term.length < 2) return [];
            return this.items.filter(i => i.haystack.includes(term)).slice(0, 8);
        }
     }"
     @click.outside="open = false">
    <form action="{{ $action ?? route('services.index') }}" method="GET">
        <div class="flex items-center overflow-hidden rounded-xl border-2 border-slate-200 bg-white shadow-sm transition-colors focus-within:border-brand-600 dark:border-slate-700 dark:bg-slate-900">
            <svg viewBox="0 0 24 24" class="ms-4 h-4 w-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
            <input type="search" name="q" x-model="q" @focus="open = true" autocomplete="off"
                placeholder="{{ $placeholder }}"
                class="min-w-0 flex-1 bg-transparent px-3 py-3.5 text-sm text-slate-900 outline-none placeholder:text-slate-400 dark:text-white {{ $isUrdu ? 'font-urdu text-right text-base' : '' }}">
            <button type="submit" class="flex-shrink-0 bg-brand-600 px-5 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-brand-700 {{ $isUrdu ? 'font-urdu text-base' : '' }}">
                {{ __('messages.landing.search_btn') }}
            </button>
        </div>
    </form>

    {{-- Live suggestions --}}
    <div x-show="open && matches.length" x-cloak
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute inset-x-0 top-full z-20 mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg dark:border-slate-700 dark:bg-slate-900">
        <template x-for="item in matches" :key="item.url">
            <a :href="item.url"
               class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-brand-50 dark:text-slate-200 dark:hover:bg-brand-950/40 {{ $isUrdu ? 'font-urdu flex-row-reverse text-right' : '' }}">
                <span x-text="item.name"></span>
                <span class="shrink-0 text-xs text-slate-400" x-text="item.category"></span>
            </a>
        </template>
    </div>
</div>
