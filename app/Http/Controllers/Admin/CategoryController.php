<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CatalogCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private CatalogCache $catalog) {}

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
        unset($data['remove_image'], $data['remove_icon']);

        $data['slug'] = Category::generateSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        $this->applyUpload($request, null, $data, 'image', 'categories');
        $this->applyUpload($request, null, $data, 'icon', 'categories');
        if (array_key_exists('icon', $data) && $data['icon'] === null) {
            $data['icon'] = 'default';
        }

        Category::create($data);
        $this->catalog->flush();

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
        unset($data['remove_image'], $data['remove_icon']);

        $data['slug'] = Category::generateSlug($data['name'], $category->id);
        $data['is_active'] = $request->boolean('is_active');

        $this->applyUpload($request, $category, $data, 'image', 'categories');
        $this->applyUpload($request, $category, $data, 'icon', 'categories');
        if (array_key_exists('icon', $data) && $data['icon'] === null) {
            $data['icon'] = 'default';
        }

        $category->update($data);
        $this->catalog->flush();

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

        foreach (['image'] as $field) {
            if ($category->{$field}) {
                Storage::disk('public')->delete($category->{$field});
            }
        }
        if ($category->icon && Str::contains($category->icon, '/')) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->delete();
        $this->catalog->flush();

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
            'icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_icon' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
