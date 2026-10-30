/* Daftarkan service worker + tombol "Pasang Aplikasi". */
(function () {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
  }

  var box = document.getElementById('pwa-install');
  if (!box) return;

  var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
  if (standalone) return; // sudah berjalan sebagai aplikasi

  var KEY = 'pwa-dismissed-until';
  function dismissed() {
    try { return Number(localStorage.getItem(KEY) || 0) > Date.now(); } catch (e) { return false; }
  }

  var deferred = null;

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferred = e;
    if (!dismissed()) box.hidden = false;
  });

  window.addEventListener('appinstalled', function () { box.hidden = true; deferred = null; });

  box.querySelector('[data-install]').addEventListener('click', function () {
    if (deferred) {
      deferred.prompt();
      deferred.userChoice.then(function () { deferred = null; box.hidden = true; });
    } else {
      alert('Untuk memasang: buka menu browser lalu pilih "Tambahkan ke Layar Utama" (di iPhone/iPad: tombol Bagikan, lalu "Tambahkan ke Layar Utama").');
    }
  });

  box.querySelector('[data-close]').addEventListener('click', function () {
    box.hidden = true;
    try { localStorage.setItem(KEY, String(Date.now() + 7 * 24 * 60 * 60 * 1000)); } catch (e) {}
  });

  // Safari iOS tidak punya prompt otomatis: tampilkan tombol berisi petunjuk.
  if (/iphone|ipad|ipod/i.test(navigator.userAgent) && !dismissed()) box.hidden = false;
})();
