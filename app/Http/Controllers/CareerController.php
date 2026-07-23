<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use App\Models\CareerCategory;
use App\Models\CareerListing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category' => ['nullable', 'exists:career_categories,id'],
            'employment_type' => ['nullable', Rule::in(CareerListing::EMPLOYMENT_TYPES)],
            'city' => ['nullable', 'string', 'max:120'],
        ]);

        $query = CareerListing::open()->with('category');

        if (! empty($validated['category'])) {
            $query->where('career_category_id', $validated['category']);
        }

        if (! empty($validated['employment_type'])) {
            $query->where('employment_type', $validated['employment_type']);
        }

        if (! empty($validated['city'])) {
            $query->where('city', $validated['city']);
        }

        $listings = $query->latest()->get();

        $categories = CareerCategory::active()->orderBy('sort_order')->orderBy('name')->get();

        $appliedIds = (auth()->check() && auth()->user()->isJobSeeker())
            ? CareerApplication::where('user_id', auth()->id())->pluck('career_listing_id')->all()
            : [];

        return view('careers.index', [
            'listings' => $listings,
            'categories' => $categories,
            'filters' => $validated,
            'appliedIds' => $appliedIds,
        ]);
    }

    public function show(CareerListing $listing): View
    {
        abort_unless($listing->isOpen(), 404);

        $listing->load('category');

        $hasApplied = auth()->check()
            ? $listing->applications()->where('user_id', auth()->id())->exists()
            : false;

        return view('careers.show', compact('listing', 'hasApplied'));
    }
}
