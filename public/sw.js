// TBS Kliniek - Service Worker for PWA (Progressive Web App)
const CACHE_NAME = 'tbs-kliniek-v1';
const PRECACHE_ASSETS = [
    '/assets/logo.png',
    '/assets/icons/icon-192x192.png',
    '/assets/icons/icon-512x512.png',
    '/manifest.webmanifest'
];

// Install: precache essential static brand assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch(() => {});
        }).then(() => self.skipWaiting())
    );
});

// Activate: clean up older caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch: Network-first for dynamic game pages; cache-first for pre-cached icons
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Only intercept GET requests
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // If fetching static icon/manifest, try cache first
    if (url.pathname.startsWith('/assets/icons/') || url.pathname.endsWith('manifest.webmanifest')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                return cached || fetch(request).then((response) => {
                    if (response && response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Default: Network first, fall back to offline if network fails
    event.respondWith(
        fetch(request).catch(() => {
            return caches.match(request);
        })
    );
});
