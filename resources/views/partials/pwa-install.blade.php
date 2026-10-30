<div id="pwa-install" hidden role="region" aria-label="Pasang aplikasi">
  <button type="button" data-install>Pasang Aplikasi</button>
  <button type="button" data-close aria-label="Tutup">&times;</button>
</div>
<style>
  #pwa-install{position:fixed;right:1rem;bottom:1rem;z-index:60;display:flex;align-items:center;background:#0F172A;color:#fff;border-radius:999px;box-shadow:0 6px 20px rgba(15,23,42,.35);font:600 .8125rem/1 Inter,system-ui,sans-serif;overflow:hidden}
  #pwa-install[hidden]{display:none}
  #pwa-install button{background:transparent;color:inherit;border:0;font:inherit;cursor:pointer}
  #pwa-install [data-install]{padding:.75rem .5rem .75rem 1rem}
  #pwa-install [data-close]{padding:.75rem 1rem .75rem .5rem;font-size:1.1rem;opacity:.7}
</style>
<script src="/js/pwa.js" defer></script>
