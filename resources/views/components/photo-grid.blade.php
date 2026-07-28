@props(['photos', 'columns' => 3])

<div x-data="{ open: false, src: null, caption: null, show(s, c) { this.src = s; this.caption = c; this.open = true; } }">
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-{{ $columns }}">
        @foreach ($photos as $photo)
            <button type="button" @click="show({{ \Illuminate\Support\Js::from($photo['url']) }}, {{ \Illuminate\Support\Js::from($photo['caption'] ?? null) }})"
               class="group relative aspect-square overflow-hidden rounded-xl">
                <img src="{{ $photo['url'] }}" alt="{{ $photo['caption'] ?? '' }}" class="h-full w-full object-cover transition group-hover:scale-105">
            </button>
        @endforeach
    </div>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         @keydown.escape.window="open = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm" @click="open = false">
        <button type="button" @click="open = false" aria-label="Close" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
        </button>
        <div class="max-h-[85vh] max-w-3xl" @click.stop>
            <img :src="src" alt="" class="max-h-[85vh] w-auto rounded-xl object-contain shadow-2xl">
            <p x-show="caption" x-text="caption" class="mt-3 text-center text-sm text-white/80"></p>
        </div>
    </div>
</div>
