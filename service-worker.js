// Nama cache
const CACHE_NAME = "educonnect-cache-v2";

// File yang mau dicache (opsional)
const FILES_TO_CACHE = [
  "/", // Halaman utama
  "/index.html",
  "/dashboard.html",
  "/styles.css",
  "/main.js"
];

// Instal service worker
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(FILES_TO_CACHE);
    })
  );
  self.skipWaiting();
});

// Aktivasi SW (hapus cache lama)
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keyList) => {
      return Promise.all(
        keyList.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch handler
self.addEventListener("fetch", (event) => {
  const url = new URL(event.request.url);

  // ⛔ Jangan intercept request ke Google / Firebase Auth
  if (
    url.origin.includes("googleapis.com") ||
    url.origin.includes("gstatic.com") ||
    url.origin.includes("firebaseapp.com")
  ) {
    return; // biarkan browser fetch langsung
  }

  // Network first untuk HTML, cache first untuk asset lain
  if (event.request.mode === "navigate") {
    event.respondWith(
      fetch(event.request).catch(() => caches.match("/index.html"))
    );
  } else {
    event.respondWith(
      caches.match(event.request).then((response) => {
        return response || fetch(event.request);
      })
    );
  }
});
