@props(['photos'])

@if ($photos->isNotEmpty())
    <div class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-5">
        @foreach ($photos as $photo)
            <a href="{{ $photo->url() }}" target="_blank" rel="noopener" class="group aspect-square overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                <img src="{{ $photo->url() }}" alt="{{ $photo->original_name }}" class="h-full w-full object-cover transition group-hover:scale-105">
            </a>
        @endforeach
    </div>
@endif
