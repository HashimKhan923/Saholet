<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceAreaController extends Controller
{
    public function index(): View
    {
        $areas = ServiceArea::orderBy('city')->get();

        return view('admin.service-areas.index', compact('areas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');

        ServiceArea::create($data);

        return back()->with('success', 'Service area added.');
    }

    public function update(Request $request, ServiceArea $serviceArea): RedirectResponse
    {
        $data = $this->validateData($request, $serviceArea);
        $data['is_active'] = $request->boolean('is_active');

        $serviceArea->update($data);

        return back()->with('success', 'Service area updated.');
    }

    public function destroy(ServiceArea $serviceArea): RedirectResponse
    {
        $serviceArea->delete();

        return back()->with('success', 'Service area removed.');
    }

    private function validateData(Request $request, ?ServiceArea $ignore = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => [
                'required', 'string', 'max:120',
                function (string $attribute, mixed $value, \Closure $fail) use ($ignore) {
                    $exists = ServiceArea::whereRaw('LOWER(city) = ?', [mb_strtolower(trim($value))])
                        ->when($ignore, fn ($q) => $q->where('id', '!=', $ignore->id))
                        ->exists();

                    if ($exists) {
                        $fail('A service area for this city already exists.');
                    }
                },
            ],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'integer', 'min:1', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}