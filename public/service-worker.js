const STATIC_CACHE = 'operacional-static-v4';
const RUNTIME_CACHE = 'operacional-runtime-v4';
const BASE_PATH = new URL(self.registration.scope).pathname;
const OFFLINE_URL = `${BASE_PATH}offline.html`;
const MANIFEST_URL = `${BASE_PATH}manifest.webmanifest`;
const ICON_URL = `${BASE_PATH}assets/images/logo.png`;

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) =>
      cache.addAll([
        OFFLINE_URL,
        MANIFEST_URL,
        ICON_URL
      ])
    )
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then(async (keys) => {
      await Promise.all(keys
        .filter((key) => key.startsWith('operacional-') && key !== STATIC_CACHE && key !== RUNTIME_CACHE)
        .map((key) => caches.delete(key)));
      await self.clients.claim();
    })
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const req = event.request;
  const url = new URL(req.url);

  if (url.origin !== self.location.origin) return;

  // Private pages must never be stored or recovered from a previous session.
  if (req.mode === 'navigate' || req.destination === 'document') {
    event.respondWith(
      fetch(req, { cache: 'no-store' })
        .catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  const publicAsset = url.pathname.startsWith(`${BASE_PATH}assets/build/`)
    || [OFFLINE_URL, MANIFEST_URL, ICON_URL].includes(url.pathname);
  if (!publicAsset) return;

  if (['style', 'script', 'image', 'font'].includes(req.destination)) {
    event.respondWith(
      fetch(req)
        .then((response) => {
          if (response.ok && !response.redirected && !response.headers.get('Content-Type')?.includes('text/html')) {
            const copy = response.clone();
            event.waitUntil(caches.open(RUNTIME_CACHE).then((cache) => cache.put(req, copy)));
          }
          return response;
        })
        .catch(async () => {
          const cached = await caches.match(req);
          return cached || fetch(req);
        })
    );
  }
});
