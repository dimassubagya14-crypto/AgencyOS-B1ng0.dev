<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        return $this->json([
            'id' => '/',
            'name' => 'B!ng0.dev — Jasa Pengembangan Web',
            'short_name' => 'B!ng0.dev',
            'description' => 'Jasa pengembangan web dan aplikasi custom.',
            'start_url' => '/',
            'scope' => '/',
        ]);
    }

    public function adminManifest(): JsonResponse
    {
        return $this->json([
            'id' => '/admin/',
            'name' => 'AgencyOS Admin — B!ng0.dev',
            'short_name' => 'AgencyOS',
            'description' => 'Panel admin AgencyOS untuk B!ng0.dev.',
            'start_url' => '/admin/dashboard',
            'scope' => '/admin/',
        ]);
    }

    public function offline(): View
    {
        return view('offline');
    }

    private function json(array $data): JsonResponse
    {
        return response()->json(array_merge([
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#0F172A',
            'lang' => 'id',
            'icons' => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], $data), 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
