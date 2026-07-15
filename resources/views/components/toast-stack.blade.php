@auth
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 px-4 sm:items-end sm:px-6">
        <template x-for="toast in $store.notifications.toasts" :key="toast.id">
            <div x-data x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="animate-fade-up pointer-events-auto w-full max-w-sm rounded-xl border border-slate-200 bg-white p-4 shadow-lg dark:border-slate-700 dark:bg-slate-800 sm:w-96">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9a6 6 0 1 1 12 0c0 5 2 6 2 6H4s2-1 2-6z" stroke-linejoin="round"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100" x-text="toast.title"></p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400" x-text="toast.body"></p>
                    </div>
                    <button type="button" @click="$store.notifications.dismissToast(toast.id)" class="flex-shrink-0 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" aria-label="Dismiss">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
@endauth
