<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        abort_unless($category->is_active, 404);

        $category->load(['services' => fn ($q) => $q->where('is_active', true)->orderBy('name')]);
        $category->services->each(fn ($service) => $service->setRelation('category', $category));

        $searchIndex = $category->services->map(fn ($service) => [
            'name' => $service->name,
            'category' => $category->name,
            'url' => route('services.show', $service),
            'haystack' => mb_strtolower($service->name . ' ' . $category->name),
        ])->values();

        return view('categories.show', compact('category', 'searchIndex'));
    }
}
