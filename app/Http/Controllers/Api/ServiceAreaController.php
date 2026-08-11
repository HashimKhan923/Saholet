<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;

class ServiceAreaController extends Controller
{
    /** Cities with an approved provider — used to populate city pickers client-side. */
    public function index(): JsonResponse
    {
        $cities = ProviderProfile::approved()->pluck('city')->filter()->unique()->sort()->values();

        return response()->json(['cities' => $cities]);
    }
}
