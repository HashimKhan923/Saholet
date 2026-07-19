<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = Category::withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($q !== '') {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where('name', 'like', $term);
        }

        $categories = $query->get();

        $counts = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'hidden' => Category::where('is_active', false)->count(),
        ];

        return view('admin.categories.index', compact('categories', 'q', 'counts'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        unset($data['remove_image'], $data['remove_banner']);

        $data['slug'] = Category::generateSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        $this->applyUpload($request, null, $data, 'image', 'categories');
        $this->applyUpload($request, null, $data, 'banner', 'categories');

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validateData($request);
        unset($data['remove_image'], $data['remove_banner']);

        $data['slug'] = Category::generateSlug($data['name'], $category->id);
        $data['is_active'] = $request->boolean('is_active');

        $this->applyUpload($request, $category, $data, 'image', 'categories');
        $this->applyUpload($request, $category, $data, 'banner', 'categories');

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->services()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Cannot delete a category that still has services. Remove or reassign its services first.');
        }

        foreach (['image', 'banner'] as $field) {
            if ($category->{$field}) {
                Storage::disk('public')->delete($category->{$field});
            }
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted.');
    }

    private function applyUpload(Request $request, ?Category $category, array &$data, string $field, string $folder): void
    {
        if ($request->hasFile($field)) {
            if ($category?->{$field}) {
                Storage::disk('public')->delete($category->{$field});
            }
            $data[$field] = $request->file($field)->store($folder, 'public');
        } elseif ($category && $request->boolean('remove_' . $field)) {
            if ($category->{$field}) {
                Storage::disk('public')->delete($category->{$field});
            }
            $data[$field] = null;
        } else {
            unset($data[$field]);
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['required', Rule::in(array_keys(config('services_catalog.icons')))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_banner' => ['nullable', 'boolean'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
