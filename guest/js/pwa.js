if ('serviceWorker' in navigator) {
     navigator.serviceWorker.register('/js/sw.js')
     .then(function(registration) {
         console.log('Service Worker 注册成功');
       })
     .catch(function(error) {
         console.log('Service Worker 注册失败：', error);
       });
   }