<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderDirectoryController extends Controller
{
    /** Public directory of approved providers, with search + city filter. */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $query = ProviderProfile::query()
            ->approved()
            ->with([
                'user:id,name',
                'providerServices' => fn ($q) => $q->where('is_active', true)->with('service:id,name,slug'),
            ]);

        if (! empty($validated['q'])) {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $validated['q']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('business_name', 'like', $term)
                    ->orWhere('bio', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term))
                    ->orWhereHas('providerServices.service', fn ($s) => $s->where('name', 'like', $term));
            });
        }

        if (! empty($validated['city'])) {
            $query->where('city', $validated['city']);
        }

        $providers = $query
            ->orderByDesc('rating_avg')
            ->orderByDesc('reviews_count')
            ->paginate(12)
            ->withQueryString();

        $cities = ProviderProfile::approved()
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('providers.index', [
            'providers' => $providers,
            'cities' => $cities,
            'filters' => $validated,
        ]);
    }

    /** Public provider profile: bio, offered services, and latest reviews. */
    public function show(ProviderProfile $provider): View
    {
        abort_unless($provider->isApproved(), 404);

        $provider->load([
            'user:id,name',
            'providerServices' => fn ($q) => $q->where('is_active', true)
                ->with('service.category')
                ->orderBy('price'),
            'portfolioPhotos',
        ]);

        $reviews = Review::with('consumer:id,name')
            ->where('provider_profile_id', $provider->id)
            ->latest()
            ->limit(10)
            ->get();

        $completedJobs = $provider->bookings()->where('status', Booking::STATUS_COMPLETED)->count();

        return view('providers.show', compact('provider', 'reviews', 'completedJobs'));
    }
}