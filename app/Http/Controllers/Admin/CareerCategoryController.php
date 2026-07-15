<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerCategoryController extends Controller
{
    public function index(): View
    {
        $categories = CareerCategory::withCount('listings')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.career-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.career-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['slug'] = CareerCategory::generateSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        CareerCategory::create($data);

        return redirect()
            ->route('admin.career-categories.index')
            ->with('success', 'Category created.');
    }

    public function edit(CareerCategory $careerCategory): View
    {
        return view('admin.career-categories.edit', ['category' => $careerCategory]);
    }

    public function update(Request $request, CareerCategory $careerCategory): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['slug'] = CareerCategory::generateSlug($data['name'], $careerCategory->id);
        $data['is_active'] = $request->boolean('is_active');

        $careerCategory->update($data);

        return redirect()
            ->route('admin.career-categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(CareerCategory $careerCategory): RedirectResponse
    {
        if ($careerCategory->listings()->exists()) {
            return redirect()
                ->route('admin.career-categories.index')
                ->with('error', 'Cannot delete a category that still has job listings. Remove or reassign its listings first.');
        }

        $careerCategory->delete();

        return redirect()
            ->route('admin.career-categories.index')
            ->with('success', 'Category deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
