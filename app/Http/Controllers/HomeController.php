<?php

namespace App\Http\Controllers;

use App\Models\Booking;
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
        $categories = $this->catalog->categories()->take(8);

        $cheapestService = $categories
            ->flatMap(fn ($category) => $category->services)
            ->sortBy('base_price')
            ->first();

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

        return view('landing', [
            'categories' => $categories,
            'cheapestService' => $cheapestService,
            'stats' => $stats,
            'testimonials' => $testimonials,
        ]);
    }
}