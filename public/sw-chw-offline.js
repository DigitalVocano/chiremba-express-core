/**
 * Chiremba Express - Frontline Community Health Worker (CHW) Offline-First Service Worker
 * Caches intake forms, maternal danger-sign checklists, and static assets for zero-connectivity clinics.
 */
const CACHE_NAME = 'chiremba-chw-offline-v1';
const OFFLINE_URLS = [
    '/chw-portal',
    '/femtech-hub',
    '/css/app.css',
    '/js/app.js'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(OFFLINE_URLS).catch(() => {});
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});

