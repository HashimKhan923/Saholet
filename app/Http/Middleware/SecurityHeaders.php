<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), microphone=(), camera=()');

        // Only attach CSP to HTML documents (skip JSON, manifest, SW, file streams).
        $contentType = (string) $response->headers->get('Content-Type');
        if (str_contains($contentType, 'text/html')) {
            $reverbHost = env('REVERB_HOST', 'localhost');
            $reverbPort = env('REVERB_PORT', 8080);
            $ws = "ws://{$reverbHost}:{$reverbPort} wss://{$reverbHost}:{$reverbPort}";

            // When `npm run dev` is running, Laravel loads assets from the Vite dev
            // server (a different origin — see public/hot) instead of /build. Without
            // allow-listing it here, the CSP silently blocks that origin's script/style/
            // HMR-websocket requests and the page renders with no JS or CSS at all.
            $viteDevOrigins = '';
            if (app()->environment('local') && is_file(public_path('hot'))) {
                $hotUrl = trim((string) file_get_contents(public_path('hot')));
                $origin = rtrim(preg_replace('/^https?:\/\//', '', $hotUrl), '/');
                if ($origin !== '') {
                    $viteDevOrigins = " http://{$origin} https://{$origin} ws://{$origin} wss://{$origin}";
                }
            }

            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$viteDevOrigins}",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com{$viteDevOrigins}",
                "font-src 'self' https://fonts.gstatic.com",
                "img-src 'self' data: https:",
                "connect-src 'self' {$ws} https://nominatim.openstreetmap.org{$viteDevOrigins}",
                "frame-ancestors 'self'",
                "base-uri 'self'",
                "form-action 'self'",
            ]);

            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}