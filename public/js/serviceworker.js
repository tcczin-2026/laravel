self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open('my-site-cache').then((cache) => {
            return cache.addAll([
                '/',
                '/css/app.css',
                '/js/app.js',
                '/offline',
            ]);
        })
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        }).catch(() => caches.match('/offline'))
    );
});