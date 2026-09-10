@props(['product', 'buttonClass' => 'rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'])

<div x-data="{ open: false, submitting: false }" class="inline-block">
    <button type="button" @click="open = true" class="{{ $buttonClass }}">Deactivate</button>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/50" x-transition.opacity @click="open = false"></div>
            <div x-show="open" x-transition
                class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-800">
                <button type="button" @click="open = false" class="absolute right-4 top-4 cursor-pointer rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-200" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
                <h3 class="pr-6 text-left font-display text-base font-bold text-slate-900 dark:text-white">Deactivate "{{ $product->name }}"?</h3>
                <p class="mt-1 text-left text-sm text-slate-500 dark:text-slate-400">It'll disappear from the shop immediately. The provider will see both notes below on their product.</p>
                <form method="POST" action="{{ route('admin.products.toggle-active', $product) }}" @submit="submitting = true" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Reason for deactivating</label>
                        <textarea name="deactivation_reason" rows="3" required
                            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            placeholder="e.g. Photos don't match the actual item"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">What the provider needs to do to reactivate it</label>
                        <textarea name="reactivation_instructions" rows="3" required
                            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            placeholder="e.g. Upload real photos of the product and re-submit for review"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                            Cancel
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-show="!submitting">Deactivate</span>
                            <span x-show="submitting" x-cloak>Please wait…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
