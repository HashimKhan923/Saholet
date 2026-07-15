<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = Service::with('category')->orderBy('name');

        if ($q !== '') {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where(fn ($w) => $w->where('name', 'like', $term)
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $term)));
        }

        $services = $query->get();

        $counts = [
            'total' => Service::count(),
            'active' => Service::where('is_active', true)->count(),
            'hidden' => Service::where('is_active', false)->count(),
        ];

        return view('admin.services.index', compact('services', 'q', 'counts'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.services.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['slug'] = Service::generateSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        Service::create($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service created.');
    }

    public function edit(Service $service): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['slug'] = Service::generateSlug($data['name'], $service->id);
        $data['is_active'] = $request->boolean('is_active');

        $service->update($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}