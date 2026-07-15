<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderPortfolioPhoto;
use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    private const MAX_PHOTOS = 12;

    public function index(Request $request): View
    {
        $profile = $this->profileFor($request);
        $photos = $profile->portfolioPhotos;

        return view('provider.portfolio', compact('profile', 'photos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $remaining = self::MAX_PHOTOS - $profile->portfolioPhotos()->count();

        if ($remaining <= 0) {
            return back()->with('error', 'You’ve reached the ' . self::MAX_PHOTOS . '-photo limit. Remove one before adding another.');
        }

        $validated = $request->validate([
            'photos' => ['required', 'array', 'max:' . $remaining],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $nextSort = (int) ($profile->portfolioPhotos()->max('sort_order') ?? 0);

        foreach ($request->file('photos', []) as $photo) {
            $path = $photo->store("provider-portfolio/{$profile->id}", 'public');

            $profile->portfolioPhotos()->create([
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'caption' => $validated['caption'] ?? null,
                'mime_type' => $photo->getClientMimeType(),
                'size' => $photo->getSize(),
                'sort_order' => ++$nextSort,
            ]);
        }

        return back()->with('success', 'Photos added to your portfolio.');
    }

    public function destroy(Request $request, ProviderPortfolioPhoto $photo): RedirectResponse
    {
        $this->profileFor($request);

        $this->authorize('delete', $photo);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Photo removed.');
    }

    private function profileFor(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}
