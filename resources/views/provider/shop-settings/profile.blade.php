@extends('layouts.provider')

@section('title', 'Shop profile — ' . config('app.name'))
@section('page_title', 'Shop profile')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Shop profile</h1>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Your shop's name and logo — this is what customers see on the Shops page and your shop's storefront.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('provider.shop-settings.profile.update') }}" enctype="multipart/form-data"
        class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <x-admin.image-upload-field
            name="logo"
            label="Shop logo"
            help="Square logo works best — recommended at least 400×400px (JPG, PNG or WebP, up to 2MB)."
            :current-url="$profile->shopLogoUrl()"
            :has-current="(bool) $profile->shop_logo"
            box="h-20 w-20"
        />

        <div>
            <label for="shop_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Shop name <span class="text-slate-400">(opt)</span></label>
            <input id="shop_name" name="shop_name" type="text" maxlength="255" value="{{ old('shop_name', $profile->shop_name) }}"
                placeholder="{{ $profile->business_name }}"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Leave blank to use your business name, "{{ $profile->business_name }}".</p>
            <x-field-error name="shop_name" />
        </div>

        <button type="submit" :disabled="submitting" class="btn-shine inline-flex items-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">Save shop profile</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
    </form>
</section>
@endsection
