<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerCategory;
use App\Models\CareerListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CareerListingController extends Controller
{
    public function index(): View
    {
        $listings = CareerListing::with('category')
            ->withCount('applications')
            ->latest()
            ->paginate(15);

        return view('admin.careers.index', compact('listings'));
    }

    public function create(): View
    {
        $categories = CareerCategory::orderBy('name')->get();

        return view('admin.careers.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['slug'] = CareerListing::generateSlug($data['title']);
        $data['is_remote'] = $request->boolean('is_remote');
        $data['posted_by'] = $request->user()->id;

        CareerListing::create($data);

        return redirect()
            ->route('admin.careers.index')
            ->with('success', 'Job listing created.');
    }

    public function edit(CareerListing $career): View
    {
        $categories = CareerCategory::orderBy('name')->get();

        return view('admin.careers.edit', ['listing' => $career, 'categories' => $categories]);
    }

    public function update(Request $request, CareerListing $career): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['slug'] = CareerListing::generateSlug($data['title'], $career->id);
        $data['is_remote'] = $request->boolean('is_remote');

        $career->update($data);

        return redirect()
            ->route('admin.careers.index')
            ->with('success', 'Job listing updated.');
    }

    public function destroy(CareerListing $career): RedirectResponse
    {
        if ($career->applications()->exists()) {
            return redirect()
                ->route('admin.careers.index')
                ->with('error', 'Cannot delete a listing that already has applications. Close it instead.');
        }

        $career->delete();

        return redirect()
            ->route('admin.careers.index')
            ->with('success', 'Job listing deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'career_category_id' => ['required', 'exists:career_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'employment_type' => ['required', Rule::in(CareerListing::EMPLOYMENT_TYPES)],
            'city' => ['nullable', 'string', 'max:120'],
            'is_remote' => ['nullable', 'boolean'],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:99999999', 'gte:salary_min'],
            'status' => ['required', Rule::in(['draft', 'open', 'closed', 'filled'])],
            'closes_at' => ['nullable', 'date'],
        ]);
    }
}
