   const CACHE_NAME = 'my-awesome-site-v1';
   const urlsToCache = [
     '/index.php',
     '/logo.png',
     '/js/pwa.js',
     '/js/sw.js',
	 '/style/tailwind.min.css',
     // 添加其他需要缓存的资源路径
   ];

   self.addEventListener('install', function(event) {
     event.waitUntil(
       caches.open(CACHE_NAME)
       .then(function(cache) {
           return cache.addAll(urlsToCache);
         })
     );
   });

   self.addEventListener('fetch', function(event) {
     event.respondWith(
       caches.match(event.request)
       .then(function(response) {
           if (response) {
             return response;
           }
           return fetch(event.request);
         })
     );
   });