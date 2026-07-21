<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CatalogCache;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /** Active categories with their active services — the browse-services catalog. */
    public function index(CatalogCache $catalog): JsonResponse
    {
        return response()->json([
            'categories' => CategoryResource::collection($catalog->categories()),
        ]);
    }
}
