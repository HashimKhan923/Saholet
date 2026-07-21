<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;

class SubscriptionPlanController extends Controller
{
    /** Public catalog of active maintenance/AMC plans. */
    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::with('service.category')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['plans' => SubscriptionPlanResource::collection($plans)]);
    }
}
