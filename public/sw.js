/* Service worker AgencyOS / B!ng0.dev
   Strategi sengaja konservatif: halaman HTML TIDAK di-cache (supaya data admin & login
   selalu segar dan tidak bocor), hanya aset statis. Saat offline tampil halaman /offline. */
const VERSION = 'v1';
const STATIC_CACHE = 'static-' + VERSION;
const OFFLINE_URL = '/offline';
const PRECACHE = [OFFLINE_URL, '/css/app.css', '/icons/icon-192.png', '/icons/icon-512.png'];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => Promise.all(PRECACHE.map((url) => cache.add(url).catch(() => null))))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys.filter((k) => k.startsWith('static-') && k !== STATIC_CACHE).map((k) => caches.delete(k))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Navigasi halaman: selalu ke jaringan; kalau gagal (offline) tampilkan halaman offline.
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match(OFFLINE_URL)));
    return;
  }

  // Aset statis: pakai cache dulu, perbarui diam-diam di belakang.
  if (/^\/(css|js|icons)\//.test(url.pathname)) {
    event.respondWith(
      caches.open(STATIC_CACHE).then((cache) =>
        cache.match(req).then((cached) => {
          const network = fetch(req)
            .then((res) => { if (res && res.ok) cache.put(req, res.clone()); return res; })
            .catch(() => cached);
          return cached || network;
        })
      )
    );
  }
});
