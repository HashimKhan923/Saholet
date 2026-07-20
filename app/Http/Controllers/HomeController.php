<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Faq;
use App\Models\ProviderProfile;
use App\Models\Review;
use App\Models\ServiceArea;
use App\Services\CatalogCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly CatalogCache $catalog)
    {
    }

    public function index(): View
    {
        $allCategories = $this->catalog->categories();
        $categories = $allCategories->take(8);

        $cheapestService = $categories
            ->flatMap(fn ($category) => $category->services)
            ->sortBy('base_price')
            ->first();

        // Flat, client-side searchable index for the hero search suggestions.
        $serviceSearchIndex = $allCategories->flatMap(function ($category) {
            return $category->services->map(fn ($service) => [
                'name' => $service->name,
                'category' => $category->name,
                'url' => route('services.show', $service),
                'haystack' => mb_strtolower($service->name . ' ' . $category->name),
            ]);
        })->values();

        // Lightweight landing stats, cached for 10 minutes.
        $stats = Cache::remember('landing.stats', 600, function (): array {
            $ratingAvg = (float) ProviderProfile::approved()->avg('rating_avg');

            return [
                'pros' => ProviderProfile::approved()->count(),
                'bookings' => Booking::where('status', Booking::STATUS_COMPLETED)->count(),
                'cities' => max(ServiceArea::count(), ProviderProfile::approved()->distinct('city')->count('city')),
                'rating' => $ratingAvg > 0 ? round($ratingAvg, 1) : 4.9,
            ];
        });

        // Real 5★ reviews power the testimonials when available.
        $testimonials = Cache::remember('landing.testimonials', 600, function () {
            return Review::with(['consumer:id,name', 'service:id,name'])
                ->where('rating', 5)
                ->whereNotNull('comment')
                ->latest()
                ->limit(3)
                ->get();
        });

        $faqs = Cache::remember('landing.faqs', 600, fn () => Faq::active()->ordered()->get());

        return view('landing', [
            'categories' => $categories,
            'cheapestService' => $cheapestService,
            'stats' => $stats,
            'testimonials' => $testimonials,
            'faqs' => $faqs,
            'serviceSearchIndex' => $serviceSearchIndex,
        ]);
    }
}