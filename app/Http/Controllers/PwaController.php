<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;

class PwaController extends Controller
{
    /**
     * Generate a dynamic web manifest for the current site
     * This allows each site to be installed as a separate PWA app
     */
    public function manifest(Request $request)
    {
        $site = $this->site;

        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }

        // Get the host to differentiate between localhost and production
        $host = $request->getHttpHost();
        $hostLabel = str_replace(['.', ':'], '-', $host); // Replace dots and colons with dashes

        $manifest = [
            'name' => $site->site_name . ' (' . $host . ')',
            'short_name' => substr($site->site_name, 0, 12),
            'description' => $site->description ?? '',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'theme_color' => $site->main_color ?? '#000000',
            'background_color' => '#ffffff',
            'icons' => [
                [
                    'src' => $this->getImageUrl($site, 'android-chrome-192x192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => $this->getImageUrl($site, 'android-chrome-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => $this->getImageUrl($site, 'android-chrome-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
            'screenshots' => [
                [
                    'src' => $this->getImageUrl($site, 'android-chrome-192x192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'form_factor' => 'narrow',
                ],
                [
                    'src' => $this->getImageUrl($site, 'android-chrome-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'form_factor' => 'wide',
                ],
            ],
            'categories' => ['productivity'],
            'shortcuts' => [
                [
                    'name' => 'Dashboard',
                    'short_name' => 'Dashboard',
                    'description' => 'Go to dashboard',
                    'url' => '/dashboard',
                    'icons' => [
                        [
                            'src' => $this->getImageUrl($site, 'android-chrome-192x192.png'),
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }

    /**
     * Serve the service worker file
     * This enables offline functionality for the PWA
     */
    public function serviceWorker(Request $request)
    {
        $site = $this->site;

        if (!$site) {
            return response('Service Worker not available', 404);
        }

        $siteName = str_replace(' ', '_', $site->site_name);
        $host = $request->getHttpHost();
        $hostLabel = str_replace(['.', ':'], '-', $host); // Replace dots and colons with dashes
        $cacheName = "pwa-cache-{$site->id}-{$siteName}-{$hostLabel}-v1";

        $serviceWorkerCode = <<<'JS'
const CACHE_NAME = 'CACHE_PLACEHOLDER';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(urlsToCache).catch((err) => {
        console.warn('Cache addAll failed:', err);
        // Continue even if some resources fail to cache
        return Promise.resolve();
      });
    })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(event.request).then((response) => {
      // Return cached response if available
      if (response) {
        return response;
      }

      // Otherwise, fetch from network
      return fetch(event.request)
        .then((response) => {
          // Don't cache non-successful responses
          if (!response || response.status !== 200 || response.type === 'error') {
            return response;
          }

          // Clone the response
          const responseToCache = response.clone();

          // Cache successful responses for GET requests
          if (event.request.method === 'GET') {
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }

          return response;
        })
        .catch(() => {
          // Return a fallback response if offline
          return caches.match('/');
        });
    })
  );
});
JS;

        $serviceWorkerCode = str_replace('CACHE_PLACEHOLDER', $cacheName, $serviceWorkerCode);

        return response($serviceWorkerCode, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Get the full URL for site images
     */
    private function getImageUrl(Site $site, string $filename): string
    {
        $photoUrl = config('app.photo_url');
        $imageFolder = $site->image_folder ?? '';

        // If logo_image is set and we're looking for it, use it directly
        if ($filename === 'logo.png' && $site->logo_image) {
            if (str_starts_with($site->logo_image, 'http')) {
                return $site->logo_image;
            }
            return $photoUrl . $site->logo_image;
        }

        // Otherwise, construct the path from image folder
        return $photoUrl . $imageFolder . $filename;
    }
}
