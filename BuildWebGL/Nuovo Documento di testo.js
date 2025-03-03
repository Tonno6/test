self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open('avatarcreator-cache').then(function(cache) {
      return cache.addAll([
        '/',
        '/index.html',
        '/Build/BuildWebGL.data.unityweb',
        '/Build/BuildWebGL.framework.js.unityweb',
        '/Build/BuildWebGL.loader.js',
        '/Build/BuildWebGL.wasm.unityweb',
        '/icon-192x192.png',
        '/icon-512x512.png',
        '/TemplateData/style.css',
        '/TemplateData/favicon.ico'
      ]);
    })
  );
});

self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request).then(function(response) {
      return response || fetch(event.request);
    })
  );
});
