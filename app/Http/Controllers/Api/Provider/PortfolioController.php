<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderPortfolioPhotoResource;
use App\Models\ProviderPortfolioPhoto;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    private const MAX_PHOTOS = 12;

    public function index(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        return response()->json(['photos' => ProviderPortfolioPhotoResource::collection($profile->portfolioPhotos)]);
    }

    /** Multipart. Body: photos[] (images, up to the remaining slots under the 12-photo cap), caption?. */
    public function store(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        $remaining = self::MAX_PHOTOS - $profile->portfolioPhotos()->count();

        if ($remaining <= 0) {
            return response()->json(['message' => 'You\'ve reached the ' . self::MAX_PHOTOS . '-photo limit. Remove one before adding another.'], 422);
        }

        $validated = $request->validate([
            'photos' => ['required', 'array', 'max:' . $remaining],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $nextSort = (int) ($profile->portfolioPhotos()->max('sort_order') ?? 0);
        $created = [];

        foreach ($request->file('photos', []) as $photo) {
            $path = $photo->store("provider-portfolio/{$profile->id}", 'public');

            $created[] = $profile->portfolioPhotos()->create([
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'caption' => $validated['caption'] ?? null,
                'mime_type' => $photo->getClientMimeType(),
                'size' => $photo->getSize(),
                'sort_order' => ++$nextSort,
            ]);
        }

        return response()->json(['photos' => ProviderPortfolioPhotoResource::collection(collect($created))], 201);
    }

    public function destroy(Request $request, ProviderPortfolioPhoto $photo): JsonResponse
    {
        $this->profileFor($request);

        $this->authorize('delete', $photo);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return response()->json(['message' => 'Photo removed.']);
    }

    private function profileFor(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}
