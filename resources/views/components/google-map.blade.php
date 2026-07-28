@props(['heading' => null, 'subheading' => null])

<div {{ $attributes->merge(['class' => 'reveal']) }}>
    @if ($heading)
        <div class="mb-6 text-center">
            <h2 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $heading }}</h2>
            @if ($subheading)
                <p class="mt-2 text-slate-600 dark:text-slate-400">{{ $subheading }}</p>
            @endif
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="aspect-[16/7] w-full">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d140765.6383632312!2d67.13077336423349!3d24.962569279684093!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3eb34b7ea3c7553b%3A0x7deaeb9437cf9ae3!2sSahoulat!5e1!3m2!1sen!2s!4v1785177357745!5m2!1sen!2s"
                width="100%"
                height="100%"
                style="border:0;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                title="Sahoulat office location on Google Maps"
                class="block h-full w-full"
            ></iframe>
        </div>
    </div>
</div>
