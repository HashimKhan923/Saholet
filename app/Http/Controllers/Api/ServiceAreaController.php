<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceArea;
use Illuminate\Http\JsonResponse;

class ServiceAreaController extends Controller
{
    /** Cities currently served — used to populate city pickers and validate coverage client-side. */
    public function index(): JsonResponse
    {
        $cities = ServiceArea::active()->pluck('city')->unique()->sort()->values();

        return response()->json(['cities' => $cities]);
    }
}
