@php
    $isEdit = isset($staff);
    $currentPermissions = $isEdit ? ($staff->permissions ?? []) : [];
    $actionLabels = ['view' => 'View', 'create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete'];
@endphp

<div class="grid gap-8 lg:grid-cols-3">
    <div class="lg:col-span-1 space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Profile</h2>

            <div class="mt-4">
                <x-admin.image-upload-field name="avatar" label="Photo" box="h-20 w-20"
                    help="Square photo, at least 200×200px. PNG, JPG or WebP, up to 4MB."
                    :current-url="$isEdit ? $staff->avatar_url : null" :has-current="$isEdit && $staff->avatar_url" remove-name="remove_avatar" />
            </div>

            <div class="mt-5">
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $isEdit ? $staff->name : '') }}" required
                    @error('name') aria-invalid="true" @enderror
                    class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                        @error('name') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                <x-field-error name="name" />
            </div>

            <div class="mt-5">
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $isEdit ? $staff->email : '') }}" required
                    @error('email') aria-invalid="true" @enderror
                    class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                        @error('email') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                <x-field-error name="email" />
            </div>

            <div class="mt-5">
                <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Phone</label>
                <input id="phone" name="phone" type="text" inputmode="numeric" value="{{ old('phone', $isEdit ? $staff->phone : '') }}"
                    data-mask="phone-pk" maxlength="12" placeholder="0300-1234567"
                    @error('phone') aria-invalid="true" @enderror
                    class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                        @error('phone') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                <x-field-error name="phone" />
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $isEdit ? 'Change password' : 'Password' }}</h2>
            @if ($isEdit)
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Leave blank to keep the current password.</p>
            @endif

            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $isEdit ? 'New password' : 'Password' }}</label>
                <div class="relative" x-data="{ show: false }">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" @if (! $isEdit) required @endif
                        @error('password') aria-invalid="true" @enderror
                        class="mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:ring-2 dark:bg-slate-950 dark:text-white
                            @error('password') border-red-400 focus:border-red-500 focus:ring-red-200 dark:border-red-500 @else border-slate-300 focus:border-brand-500 focus:ring-brand-200 dark:border-slate-700 @enderror">
                    <x-password-toggle-button />
                </div>
                <x-field-error name="password" />
            </div>

            <div class="mt-4">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirm password</label>
                <div class="relative" x-data="{ show: false }">
                    <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" @if (! $isEdit) required @endif
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <x-password-toggle-button />
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Page access</h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Choose which admin pages this staff member can open, and what they're allowed to do on each one. Unchecked pages won't even appear in their sidebar.</p>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-900 dark:text-slate-100">
                        <tr>
                            <th class="py-2 pr-4">Page</th>
                            @foreach ($actionLabels as $label)
                                <th class="px-3 py-2 text-center">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($modules as $key => $module)
                            <tr>
                                <td class="py-2.5 pr-4 font-medium text-slate-700 dark:text-slate-300">{{ $module['label'] }}</td>
                                @foreach ($actionLabels as $action => $label)
                                    <td class="px-3 py-2.5 text-center">
                                        @if (in_array($action, $module['actions'], true))
                                            <input type="checkbox" name="permissions[{{ $key }}][]" value="{{ $action }}"
                                                @checked(in_array($action, $currentPermissions[$key] ?? [], true))
                                                class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-600 dark:bg-slate-800">
                                        @else
                                            <span class="text-slate-200 dark:text-slate-700">&mdash;</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-8 flex items-center gap-3">
    <button type="submit" class="btn-shine rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
        {{ $isEdit ? 'Save changes' : 'Create staff account' }}
    </button>
    <a href="{{ route('admin.staff.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
        Cancel
    </a>
</div>
