<?php

namespace App\Http\Controllers;

use App\Models\ProviderService;
use App\Models\Service;
use App\Services\CatalogCache;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private CatalogCache $catalog) {}

    public function index(): View
    {
        $categories = $this->catalog->categories();

        return view('services.index', compact('categories'));
    }

    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        $service->load('category');

        $providers = ProviderService::with('providerProfile.user')
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->whereHas('providerProfile', fn ($q) => $q->where('status', 'approved'))
            ->orderBy('price')
            ->paginate(15);

        $related = Service::where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(3)
            ->get();

        return view('services.show', compact('service', 'providers', 'related'));
    }
}