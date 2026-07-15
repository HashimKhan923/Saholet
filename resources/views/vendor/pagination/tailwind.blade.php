@if ($paginator->hasPages())
    <nav class="mt-8 flex items-center justify-between border-t border-slate-100 pt-4" role="navigation">
        <div class="flex flex-1 items-center justify-between">
            <div>
                <p class="text-xs text-slate-500">
                    {{ __('Showing') }}
                    <span class="font-medium text-slate-700">{{ $paginator->firstItem() ?? 0 }}</span>
                    –
                    <span class="font-medium text-slate-700">{{ $paginator->lastItem() ?? 0 }}</span>
                    {{ __('of') }}
                    <span class="font-medium text-slate-700">{{ $paginator->total() }}</span>
                </p>
            </div>

            <div class="flex items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-300">←</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">←</a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-sm text-slate-400">{{ $element }}</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">→</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-300">→</span>
                @endif
            </div>
        </div>
    </nav>
@endif