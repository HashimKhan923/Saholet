<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = User::where('role', User::ROLE_STAFF)->latest('id');

        if ($q !== '') {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where(fn ($w) => $w->where('name', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('phone', 'like', $term));
        }

        $staff = $query->paginate(15)->withQueryString();
        $modules = config('admin_modules');

        return view('admin.staff.index', compact('staff', 'q', 'modules'));
    }

    public function create(): View
    {
        $modules = config('admin_modules');

        return view('admin.staff.create', compact('modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        unset($data['remove_avatar']);

        $data['role'] = User::ROLE_STAFF;
        $data['permissions'] = $this->sanitizePermissions($request->input('permissions', []));

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        User::create($data);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff account created.');
    }

    public function edit(User $staff): View
    {
        $this->ensureStaff($staff);

        $modules = config('admin_modules');

        return view('admin.staff.edit', compact('staff', 'modules'));
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $this->ensureStaff($staff);

        $data = $this->validateData($request, $staff);
        unset($data['remove_avatar']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['permissions'] = $this->sanitizePermissions($request->input('permissions', []));


        if ($request->hasFile('avatar')) {
            if ($staff->avatar) {
                Storage::disk('public')->delete($staff->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            if ($staff->avatar) {
                Storage::disk('public')->delete($staff->avatar);
            }
            $data['avatar'] = null;
        } else {
            unset($data['avatar']);
        }

        $staff->update($data);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff account updated.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $this->ensureStaff($staff);

        if ($staff->avatar) {
            Storage::disk('public')->delete($staff->avatar);
        }
        $staff->delete();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff account deleted.');
    }

    public function suspend(User $staff): RedirectResponse
    {
        $this->ensureStaff($staff);

        $staff->update(['suspended_at' => now()]);

        return back()->with('success', $staff->name . ' has been suspended.');
    }

    public function unsuspend(User $staff): RedirectResponse
    {
        $this->ensureStaff($staff);

        $staff->update(['suspended_at' => null]);

        return back()->with('success', $staff->name . ' has been reinstated.');
    }

    private function ensureStaff(User $staff): void
    {
        abort_unless($staff->role === User::ROLE_STAFF, 404);
    }

    private function validateData(Request $request, ?User $staff = null): array
    {
        // password is hashed automatically via User's 'hashed' cast on save.
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$staff ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Only keep actions that actually exist for each module (per
     * config/admin_modules.php) — never trust the raw request payload.
     */
    private function sanitizePermissions(array $submitted): array
    {
        $permissions = [];

        foreach (config('admin_modules') as $module => $config) {
            $allowed = array_intersect((array) ($submitted[$module] ?? []), $config['actions']);
            if ($allowed !== []) {
                $permissions[$module] = array_values($allowed);
            }
        }

        return $permissions;
    }
}
