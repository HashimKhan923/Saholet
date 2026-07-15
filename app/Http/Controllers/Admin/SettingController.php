<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CommissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $commissionRate = Setting::get('commission_rate', CommissionService::DEFAULT_RATE);
        $geofencingEnabled = Setting::bool('geofencing_enabled', false);

        return view('admin.settings.edit', compact('commissionRate', 'geofencingEnabled'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:50'],
            'geofencing_enabled' => ['nullable', 'boolean'],
        ]);

        Setting::put('commission_rate', $data['commission_rate']);
        Setting::put('geofencing_enabled', $request->boolean('geofencing_enabled') ? '1' : '0');

        return back()->with('success', 'Settings saved.');
    }
}