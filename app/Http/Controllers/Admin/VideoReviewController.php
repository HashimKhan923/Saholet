<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VideoReviewController extends Controller
{
    public function index(): View
    {
        $videoReviews = VideoReview::orderBy('sort_order')->get();

        $counts = [
            'total' => VideoReview::count(),
            'active' => VideoReview::where('is_active', true)->count(),
            'hidden' => VideoReview::where('is_active', false)->count(),
        ];

        return view('admin.video-reviews.index', compact('videoReviews', 'counts'));
    }

    public function create(): View
    {
        return view('admin.video-reviews.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');

        VideoReview::create($data);

        return redirect()
            ->route('admin.video-reviews.index')
            ->with('success', 'Video review created.');
    }

    public function edit(VideoReview $videoReview): View
    {
        return view('admin.video-reviews.edit', compact('videoReview'));
    }

    public function update(Request $request, VideoReview $videoReview): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');

        $videoReview->update($data);

        return redirect()
            ->route('admin.video-reviews.index')
            ->with('success', 'Video review updated.');
    }

    public function destroy(VideoReview $videoReview): RedirectResponse
    {
        $videoReview->delete();

        return redirect()
            ->route('admin.video-reviews.index')
            ->with('success', 'Video review deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'youtube_url' => ['required', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $youtubeId = VideoReview::extractYoutubeId($data['youtube_url']);

        if (! $youtubeId) {
            throw ValidationException::withMessages([
                'youtube_url' => 'That doesn\'t look like a valid YouTube link. Paste the full video URL (e.g. https://www.youtube.com/watch?v=... or https://youtu.be/...).',
            ]);
        }

        $data['youtube_id'] = $youtubeId;

        return $data;
    }
}
