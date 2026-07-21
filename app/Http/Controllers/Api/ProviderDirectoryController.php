<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderProfileResource;
use App\Http\Resources\ReviewResource;
use App\Models\ProviderProfile;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderDirectoryController extends Controller
{
    /** Public directory of approved providers, with optional search + city filter. */
    public function index(Request $request): JsonResponse
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

        return response()->json([
            'providers' => ProviderProfileResource::collection($providers),
            'pagination' => [
                'current_page' => $providers->currentPage(),
                'last_page' => $providers->lastPage(),
                'total' => $providers->total(),
            ],
            'cities' => $cities,
        ]);
    }

    /** Public provider profile: bio, offered services, portfolio, and latest reviews. */
    public function show(ProviderProfile $provider): JsonResponse
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

        return response()->json([
            'provider' => new ProviderProfileResource($provider),
            'reviews' => ReviewResource::collection($reviews),
        ]);
    }
}
