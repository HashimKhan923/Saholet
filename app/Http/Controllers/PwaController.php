<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        return response()->json([
            'id' => '/',
            'name' => config('app.name'),
            'short_name' => config('app.name'),
            'description' => 'On-demand home services across Pakistan.',
            'lang' => app()->getLocale(),
            'start_url' => '/?source=pwa',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#f8fafc',
            'theme_color' => '#1a7a35',
            'icons' => [
                ['src' => '/icons/icon.svg', 'sizes' => '192x192', 'type' => 'image/svg+xml', 'purpose' => 'any'],
                ['src' => '/icons/icon.svg', 'sizes' => '512x512', 'type' => 'image/svg+xml', 'purpose' => 'any'],
                ['src' => '/icons/icon.svg', 'sizes' => '512x512', 'type' => 'image/svg+xml', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                ['name' => 'Browse services', 'url' => '/services', 'icons' => [['src' => '/icons/icon.svg', 'sizes' => '96x96']]],
                ['name' => 'My bookings', 'url' => '/bookings', 'icons' => [['src' => '/icons/icon.svg', 'sizes' => '96x96']]],
                ['name' => 'Emergency help', 'url' => '/emergencies', 'icons' => [['src' => '/icons/icon.svg', 'sizes' => '96x96']]],
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE)
            ->header('Content-Type', 'application/manifest+json');
    }
}