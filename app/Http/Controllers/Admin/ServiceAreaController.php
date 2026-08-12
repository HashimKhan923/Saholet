<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceArea;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceAreaController extends Controller
{
    public function index(): View
    {
        $areas = ServiceArea::orderBy('name')->get();
        $geofencingEnabled = Setting::bool('geofencing_enabled', false);

        return view('admin.service-areas.index', compact('areas', 'geofencingEnabled'));
    }

    /** Single on/off switch, shown at the top of this page rather than a separate Settings page — it only ever governs whether the areas below are enforced, so it belongs right next to them. */
    public function toggleGeofencing(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'geofencing_enabled' => ['required', 'boolean'],
        ]);

        Setting::put('geofencing_enabled', $data['geofencing_enabled'] ? '1' : '0');

        return back()->with('success', $data['geofencing_enabled'] ? 'Geo-fencing turned on.' : 'Geo-fencing turned off.');
    }

    public function create(): View
    {
        return view('admin.service-areas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');

        ServiceArea::create($data);

        return redirect()->route('admin.service-areas.index')->with('success', 'Service area added.');
    }

    public function edit(ServiceArea $serviceArea): View
    {
        return view('admin.service-areas.edit', compact('serviceArea'));
    }

    public function update(Request $request, ServiceArea $serviceArea): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');

        $serviceArea->update($data);

        return redirect()->route('admin.service-areas.index')->with('success', 'Service area updated.');
    }

    public function destroy(ServiceArea $serviceArea): RedirectResponse
    {
        $serviceArea->delete();

        return back()->with('success', 'Service area removed.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'integer', 'min:1', 'max:500'],
            'boundary' => [
                'required', 'json',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $points = json_decode((string) $value, true);
                    if (! is_array($points) || count($points) < 3) {
                        $fail('Draw a boundary with at least 3 points — a service area with no boundary can never match anything.');

                        return;
                    }
                    foreach ($points as $point) {
                        if (! is_array($point) || ! isset($point['lat'], $point['lng'])) {
                            $fail('The boundary is malformed — try redrawing it.');

                            return;
                        }
                    }
                },
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['boundary'] = json_decode($data['boundary'], true);

        return $data;
    }
}
