<?php

namespace App\Http\Controllers;

use App\Models\CareerListing;
use App\Models\Service;
use Illuminate\Http\Response;

class PublicController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /dashboard',
            'Disallow: /admin',
            'Disallow: /provider',
            'Disallow: /job-seeker',
            'Disallow: /bookings',
            'Disallow: /jobs',
            'Disallow: /emergencies',
            'Disallow: /notifications',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function sitemap(): Response
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => route('services.index'), 'priority' => '0.9'],
            ['loc' => route('careers.index'), 'priority' => '0.7'],
        ];

        foreach (Service::where('is_active', true)->get() as $service) {
            $urls[] = ['loc' => route('services.show', $service), 'priority' => '0.7'];
        }

        foreach (CareerListing::open()->get() as $listing) {
            $urls[] = ['loc' => route('careers.show', $listing), 'priority' => '0.6'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url><loc>" . e($u['loc']) . "</loc><priority>{$u['priority']}</priority></url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}