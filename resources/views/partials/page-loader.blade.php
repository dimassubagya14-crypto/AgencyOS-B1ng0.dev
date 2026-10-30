@php
    $minFirst = $loaderMin ?? 700;      // ms, kunjungan pertama dalam sesi browser
    $minRepeat = $loaderMinRepeat ?? 300; // ms, halaman berikutnya
    $word = str_split('B!ng0.dev');
@endphp
<div id="page-loader" role="status" aria-live="polite" aria-label="Memuat halaman" data-min="{{ $minFirst }}" data-min-repeat="{{ $minRepeat }}">
  <div class="pl-logo" aria-hidden="true">
    @foreach ($word as $i => $ch)
      <span class="{{ $i >= 5 ? 'pl-accent' : '' }}" style="--i:{{ $i }}">{{ $ch }}</span>
    @endforeach
  </div>
  <div class="pl-sub" aria-hidden="true">Memuat<i>.</i><i>.</i><i>.</i></div>
</div>
<noscript><style>#page-loader{display:none!important}</style></noscript>
<style>
  #page-loader{position:fixed;top:0;right:0;bottom:0;left:0;z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.75rem;background:#0F172A;color:#fff;font-family:"Space Grotesk",Inter,system-ui,sans-serif;transition:opacity .35s ease,visibility .35s ease;animation:pl-failsafe .4s ease 5s forwards}
  #page-loader.is-done{opacity:0;visibility:hidden;pointer-events:none}
  .pl-logo{display:flex;font-size:clamp(2rem,7vw,3.25rem);font-weight:700;letter-spacing:-.02em;line-height:1}
  .pl-logo span{display:inline-block;opacity:0;transform:translateY(.6em);animation:pl-letter 1.4s cubic-bezier(.2,.8,.2,1) infinite;animation-delay:calc(var(--i) * 70ms)}
  .pl-logo .pl-accent{color:#3B82F6}
  .pl-sub{font:500 .75rem "JetBrains Mono",ui-monospace,monospace;letter-spacing:.2em;text-transform:uppercase;opacity:.75}
  .pl-sub i{font-style:normal;animation:pl-dot 1.2s infinite both}
  .pl-sub i:nth-child(2){animation-delay:.2s}
  .pl-sub i:nth-child(3){animation-delay:.4s}
  @keyframes pl-letter{0%{opacity:0;transform:translateY(.6em)}25%,70%{opacity:1;transform:none}100%{opacity:0;transform:translateY(-.3em)}}
  @keyframes pl-dot{0%,80%,100%{opacity:.2}40%{opacity:1}}
  @keyframes pl-failsafe{to{opacity:0;visibility:hidden;pointer-events:none}}
  @media (prefers-reduced-motion:reduce){.pl-logo span{animation:none;opacity:1;transform:none}.pl-sub i{animation:none;opacity:1}}
</style>
<script>
  (function () {
    var el = document.getElementById('page-loader');
    if (!el) return;
    var started = Date.now();
    var seen = false;
    try { seen = sessionStorage.getItem('pl-seen') === '1'; sessionStorage.setItem('pl-seen', '1'); } catch (e) {}
    var minMs = Number(seen ? el.getAttribute('data-min-repeat') : el.getAttribute('data-min')) || 0;

    function hide() {
      el.classList.add('is-done');
      setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 500);
    }
    function done() {
      setTimeout(hide, Math.max(0, minMs - (Date.now() - started)));
    }
    if (document.readyState === 'complete') done(); else window.addEventListener('load', done);
  })();
</script>
