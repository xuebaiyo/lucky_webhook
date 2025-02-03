   const CACHE_NAME = 'my-awesome-site-v1';
   const urlsToCache = [
     '/guest',
     '/guest/index.php',
     '/guest/api.php',
     '/guest/logo.png',
     '/guest/js/pwa.js',
     '/guest/js/sw.js',
     '/guest/project/config.php',
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