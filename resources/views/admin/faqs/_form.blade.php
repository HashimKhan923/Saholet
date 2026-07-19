@php
    $checked = (bool) old('is_active', $faq?->is_active ?? true);
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400">
        Please fix the highlighted fields below.
    </div>
@endif

<form method="POST" action="{{ $action }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">English</p>

        <div>
            <label for="question_en" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Question</label>
            <input id="question_en" name="question_en" type="text" value="{{ old('question_en', $faq?->question_en) }}" required
                @error('question_en') aria-invalid="true" @enderror
                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                    @error('question_en') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
            <x-field-error name="question_en" />
        </div>

        <div class="mt-4">
            <label for="answer_en" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Answer</label>
            <textarea id="answer_en" name="answer_en" rows="3" required
                @error('answer_en') aria-invalid="true" @enderror
                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                    @error('answer_en') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('answer_en', $faq?->answer_en) }}</textarea>
            <x-field-error name="answer_en" />
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">اردو (Urdu) <span class="normal-case text-slate-400">— optional, falls back to English</span></p>

        <div>
            <label for="question_ur" class="block text-sm font-medium text-slate-700 dark:text-slate-300">سوال (Question)</label>
            <input id="question_ur" name="question_ur" type="text" dir="rtl" value="{{ old('question_ur', $faq?->question_ur) }}"
                @error('question_ur') aria-invalid="true" @enderror
                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm font-urdu text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                    @error('question_ur') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
            <x-field-error name="question_ur" />
        </div>

        <div class="mt-4">
            <label for="answer_ur" class="block text-sm font-medium text-slate-700 dark:text-slate-300">جواب (Answer)</label>
            <textarea id="answer_ur" name="answer_ur" rows="3" dir="rtl"
                @error('answer_ur') aria-invalid="true" @enderror
                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm font-urdu text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                    @error('answer_ur') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">{{ old('answer_ur', $faq?->answer_ur) }}</textarea>
            <x-field-error name="answer_ur" />
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="sort_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Sort order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $faq?->sort_order ?? 0) }}" required
                @error('sort_order') aria-invalid="true" @enderror
                class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-900 dark:text-white
                    @error('sort_order') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
            <x-field-error name="sort_order" />
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="is_active" value="1" @checked($checked)
            class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-600 dark:bg-slate-800">
        Active (visible on the website)
    </label>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" :disabled="submitting"
            class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">{{ $submitLabel }}</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
        <a href="{{ route('admin.faqs.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
    </div>
</form>
