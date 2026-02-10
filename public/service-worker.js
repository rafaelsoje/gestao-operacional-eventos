const STATIC_CACHE = 'operacional-static-v2';
const RUNTIME_CACHE = 'operacional-runtime-v2';
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
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== STATIC_CACHE && key !== RUNTIME_CACHE)
          .map((key) => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const req = event.request;
  const url = new URL(req.url);

  if (url.origin !== self.location.origin) return;

  // Páginas HTML: rede primeiro para evitar conteúdo antigo em telas autenticadas.
  if (req.mode === 'navigate' || req.destination === 'document') {
    event.respondWith(
      fetch(req)
        .then((response) => {
          const copy = response.clone();
          caches.open(RUNTIME_CACHE).then((cache) => cache.put(req, copy));
          return response;
        })
        .catch(async () => {
          const cached = await caches.match(req);
          return cached || caches.match(OFFLINE_URL);
        })
    );
    return;
  }

  // Assets estáticos: cache primeiro + atualização em background.
  if (['style', 'script', 'image', 'font'].includes(req.destination)) {
    event.respondWith(
      caches.match(req).then((cached) => {
        const networkFetch = fetch(req)
          .then((response) => {
            const copy = response.clone();
            caches.open(RUNTIME_CACHE).then((cache) => cache.put(req, copy));
            return response;
          })
          .catch(() => cached);

        return cached || networkFetch;
      })
    );
  }
});
