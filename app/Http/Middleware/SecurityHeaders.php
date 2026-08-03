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

            // Firebase Phone Auth's reCAPTCHA check calls Google's Identity Toolkit
            // API and loads the reCAPTCHA script/iframe from these origins — without
            // them, both the config fetch and the fallback reCAPTCHA v2 widget are
            // silently blocked, and "Send verification code" fails with no clear error.
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://maps.googleapis.com https://maps.gstatic.com https://www.gstatic.com https://www.google.com https://www.recaptcha.net{$viteDevOrigins}",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://www.gstatic.com{$viteDevOrigins}",
                "font-src 'self' https://fonts.gstatic.com",
                "img-src 'self' data: blob: https:",
                "connect-src 'self' {$ws} https://nominatim.openstreetmap.org https://maps.googleapis.com https://maps.gstatic.com https://identitytoolkit.googleapis.com https://securetoken.googleapis.com https://www.googleapis.com https://www.google.com{$viteDevOrigins}",
                "frame-src 'self' https://www.google.com https://www.recaptcha.net https://maps.google.com",
                "frame-ancestors 'self'",
                "base-uri 'self'",
                "form-action 'self'",
            ]);

            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}