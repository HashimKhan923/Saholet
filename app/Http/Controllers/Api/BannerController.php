<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    /** Active homepage banners, in display order — same set shown on the web landing page. */
    public function index(): JsonResponse
    {
        $banners = Banner::active()->ordered()->get(['id', 'image', 'link_url', 'sort_order']);

        return response()->json([
            'banners' => $banners->map(fn (Banner $banner) => [
                'id' => $banner->id,
                'image_url' => $banner->image_url,
                'link_url' => $banner->link_url,
                'sort_order' => $banner->sort_order,
            ]),
        ]);
    }
}
