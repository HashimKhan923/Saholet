<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::ordered()->get();

        $counts = [
            'total' => Brand::count(),
            'active' => Brand::where('is_active', true)->count(),
            'hidden' => Brand::where('is_active', false)->count(),
        ];

        return view('admin.brands.index', compact('brands', 'counts'));
    }

    public function create(): View
    {
        return view('admin.brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, false);
        $data['is_active'] = $request->boolean('is_active');
        $data['logo'] = $request->file('logo')->store('brands', 'public');

        Brand::create($data);

        return redirect()->route('admin.brands.index')->with('success', 'Brand added.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $this->validateData($request, true);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('logo')) {
            Storage::disk('public')->delete($brand->logo);
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        } else {
            unset($data['logo']);
        }

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        Storage::disk('public')->delete($brand->logo);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted.');
    }

    private function validateData(Request $request, bool $isUpdate): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
