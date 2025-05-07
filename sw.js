// sw.js — minimal “network first” service worker
self.addEventListener('install', evt => {
    self.skipWaiting();
  });
  self.addEventListener('activate', evt => {
    clients.claim();
  });
  self.addEventListener('fetch', evt => {
    evt.respondWith(
      fetch(evt.request).catch(() => caches.match(evt.request))
    );
  });
  