{{-- Expects to sit inside a `<div class="relative" x-data="{ show: false }">` wrapping a password input. --}}
<button type="button" @click="show = !show" tabindex="-1"
    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 transition hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300">
    <svg x-show="!show" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke-linejoin="round"/>
        <circle cx="12" cy="12" r="3"/>
    </svg>
    <svg x-show="show" x-cloak viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.24 4.24M9.9 4.24A10.4 10.4 0 0 1 12 4c6.5 0 10 7 10 7a13.2 13.2 0 0 1-3.15 4.14M6.5 6.5A13.6 13.6 0 0 0 2 11s3.5 7 10 7c1.4 0 2.7-.3 3.85-.85" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span class="sr-only" x-text="show ? 'Hide password' : 'Show password'"></span>
</button>
