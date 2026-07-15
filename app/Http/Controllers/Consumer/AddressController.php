<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        $addresses = $request->user()->addresses;

        return view('consumer.addresses.index', compact('addresses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($request, $data) {
            if ($data['is_default'] ?? false) {
                $request->user()->addresses()->update(['is_default' => false]);
            }

            $request->user()->addresses()->create($data);
        });

        return back()->with('success', 'Address saved.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);

        $data = $this->validateData($request);

        DB::transaction(function () use ($request, $address, $data) {
            if ($data['is_default'] ?? false) {
                $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($data);
        });

        return back()->with('success', 'Address updated.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->authorize('delete', $address);

        $address->delete();

        return back()->with('success', 'Address removed.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['is_default'] = $request->boolean('is_default');

        return $data;
    }
}
