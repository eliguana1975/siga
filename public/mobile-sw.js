const CACHE_NAME = 'siga-mobile-v8';
const scopeRoot = self.registration.scope.replace(/\/$/, '');
const ASSETS = [
  `${scopeRoot}/mobile`,
  `${scopeRoot}/mobile-app/app.css`,
  `${scopeRoot}/mobile-app/app.js`,
  `${scopeRoot}/mobile-app/manifest.webmanifest`
];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS)));
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))))
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);
  const isMobileAsset = url.pathname.includes('/mobile-app/') || url.pathname.endsWith('/mobile') || url.pathname.endsWith('/mobile-sw.js');

  if (!isMobileAsset || request.method !== 'GET' || request.url.includes('/api/') || request.url.includes('/mobile/auth/')) {
    return;
  }

  event.respondWith(
    fetch(request).then(response => {
      const copy = response.clone();
      caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
      return response;
    }).catch(() => caches.match(request).then(cached => cached || caches.match(`${scopeRoot}/mobile`)))
  );
});
