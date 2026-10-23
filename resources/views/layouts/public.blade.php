<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>@yield('title', 'B!ng0.dev — Jasa Pengembangan Web & Aplikasi Custom')</title>
<meta name="description" content="@yield('description', 'B!ng0.dev membantu bisnis Anda dengan company profile, e-commerce, sistem informasi, hingga dashboard custom.')" />
<meta name="robots" content="index, follow" />
<link rel="canonical" href="{{ url()->current() }}" />
<meta property="og:title" content="@yield('title', 'B!ng0.dev')" />
<meta property="og:description" content="@yield('description', '')" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ url()->current() }}" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet" />
<link rel="stylesheet" href="/css/app.css" />
@stack('head')
</head>
<body class="font-body text-on-surface antialiased">

<header class="glass sticky top-0 z-40 border-b border-outline-variant">
  <div class="max-w-6xl mx-auto px-5 md:px-10 h-[64px] md:h-[72px] flex items-center justify-between">
    <a href="{{ route('home') }}" class="font-display font-bold text-lg md:text-xl tracking-tight text-primary">B!ng0<span class="text-secondary-bright">.dev</span></a>
    <nav class="hidden md:flex items-center gap-8 font-mono text-xs uppercase tracking-wider">
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-primary' }}">Beranda</a>
      <a href="{{ route('portfolio.index') }}" class="{{ request()->routeIs('portfolio.*') ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-primary' }}">Portfolio</a>
    </nav>
    <div class="flex items-center gap-2">
      <button type="button" class="theme-toggle theme-toggle-public w-9 h-9 flex items-center justify-center rounded border border-outline-variant text-primary" aria-label="Ganti mode tampilan" title="Ganti mode tampilan">
        <span class="material-symbols-outlined theme-icon text-[20px]">dark_mode</span>
      </button>
      <a href="{{ route('requests.create') }}" class="bg-primary text-on-primary px-3.5 py-2 md:px-4 md:py-2 rounded text-xs md:text-sm font-semibold hover:bg-inverse-surface transition-colors">Pesan Jasa</a>
      <button type="button" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')" class="md:hidden w-9 h-9 flex items-center justify-center rounded border border-outline-variant text-primary" aria-label="Menu">
        <span class="material-symbols-outlined text-[20px]">menu</span>
      </button>
    </div>
  </div>
  <nav id="mobile-nav" class="md:hidden hidden border-t border-outline-variant bg-surface-container-high">
    <a href="{{ route('home') }}" class="block px-5 py-3 text-sm font-mono uppercase tracking-wider border-b border-outline-variant">Beranda</a>
    <a href="{{ route('portfolio.index') }}" class="block px-5 py-3 text-sm font-mono uppercase tracking-wider">Portfolio</a>
  </nav>
</header>

@if (session('success'))
  <div class="max-w-6xl mx-auto px-5 md:px-10 mt-4">
    <div class="bg-success-container text-success px-4 py-3 rounded-lg text-sm font-medium">{{ session('success') }}</div>
  </div>
@endif

<main>
@yield('content')
</main>

<footer class="public-footer bg-surface-container-highest border-t border-outline-variant mt-20">
  <div class="max-w-6xl mx-auto px-5 md:px-10 py-14 grid grid-cols-1 md:grid-cols-4 gap-8">
    <div class="md:col-span-2">
      <div class="font-display font-bold text-xl text-primary mb-3">{{ $profile->footer_title ?: $profile->agency_name }}</div>
      <p class="text-sm text-on-surface-variant max-w-sm">{{ $profile->footer_description ?: $profile->about }}</p>
      @if ($profile->footer_address ?: $profile->contact_address)
        <p class="text-sm text-on-surface-variant max-w-sm mt-4">{{ $profile->footer_address ?: $profile->contact_address }}</p>
      @endif
    </div>
    <div>
      <div class="font-mono text-xs uppercase tracking-wider text-on-surface-variant mb-3">Navigasi</div>
      <div class="flex flex-col gap-2 text-sm">
        <a href="{{ route('home') }}" class="hover:text-secondary">Beranda</a>
        <a href="{{ route('portfolio.index') }}" class="hover:text-secondary">Portfolio</a>
        <a href="{{ route('requests.create') }}" class="hover:text-secondary">Pesan Jasa</a>
      </div>
    </div>
    <div>
      <div class="font-mono text-xs uppercase tracking-wider text-on-surface-variant mb-3">Kontak</div>
      <div class="flex flex-col gap-2 text-sm">
        @php
          $wa = preg_replace('/\D+/', '', (string) ($profile->footer_whatsapp ?: $profile->contact_phone));
          if (str_starts_with($wa, '0')) $wa = '62' . substr($wa, 1);
          $footerEmail = $profile->footer_email ?: $profile->contact_email;
        @endphp
        @if ($wa)
          <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener noreferrer" class="hover:text-secondary">WhatsApp</a>
        @endif
        @if ($footerEmail)
          <a href="mailto:{{ $footerEmail }}" class="hover:text-secondary">{{ $footerEmail }}</a>
        @endif
      </div>
    </div>
  </div>
  <div class="border-t border-outline-variant py-5 text-center text-xs text-on-surface-variant font-mono">© {{ date('Y') }} {{ $profile->agency_name }}. All rights reserved.</div>
</footer>

<div id="lightbox" class="hidden fixed inset-0 z-[100] bg-black/80 items-center justify-center p-4" onclick="closeLightbox()">
  <img id="lightbox-img" src="" alt="" class="max-w-full max-h-full rounded-lg" />
  <button onclick="closeLightbox()" class="fixed top-4 right-4 text-white w-10 h-10 flex items-center justify-center rounded-full bg-white/10">
    <span class="material-symbols-outlined">close</span>
  </button>
</div>

<script>
(function () {
  const key = 'bing0-theme';
  function applyTheme() {
    const dark = localStorage.getItem(key) === 'dark';
    document.documentElement.classList.toggle('dark-theme', dark);
    document.body.classList.toggle('dark-theme', dark);
    document.querySelectorAll('.theme-toggle').forEach(function (button) {
      const icon = button.querySelector('.theme-icon');
      if (icon) icon.textContent = dark ? 'light_mode' : 'dark_mode';
      button.setAttribute('aria-label', dark ? 'Gunakan mode light' : 'Gunakan mode dark');
      button.setAttribute('title', dark ? 'Mode light' : 'Mode dark');
    });
  }
  applyTheme();
  document.addEventListener('DOMContentLoaded', function () {
    applyTheme();
    document.querySelectorAll('.theme-toggle').forEach(function (button) {
      button.addEventListener('click', function () {
        localStorage.setItem(key, localStorage.getItem(key) === 'dark' ? 'light' : 'dark');
        applyTheme();
      });
    });
  });
})();
</script>
<script>
  // Fade-in saat elemen masuk viewport — dipakai di kartu portfolio, stats, tim.
  document.addEventListener('DOMContentLoaded', function () {
    var els = document.querySelectorAll('.fade-in-scroll');
    if ('IntersectionObserver' in window && els.length) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('fade-in-visible');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15 });
      els.forEach(function (el) { io.observe(el); });
    } else {
      els.forEach(function (el) { el.classList.add('fade-in-visible'); });
    }

    // Counter angka naik dari 0 ke data-count saat terlihat.
    var counters = document.querySelectorAll('.counter');
    if ('IntersectionObserver' in window && counters.length) {
      var cio = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          var target = parseInt(el.getAttribute('data-count'), 10) || 0;
          var start = 0;
          var duration = 900;
          var startTime = null;
          function step(ts) {
            if (!startTime) startTime = ts;
            var progress = Math.min((ts - startTime) / duration, 1);
            el.textContent = Math.floor(progress * target);
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target;
          }
          requestAnimationFrame(step);
          cio.unobserve(el);
        });
      }, { threshold: 0.4 });
      counters.forEach(function (el) { cio.observe(el); });
    }
  });

  // Lightbox sederhana untuk galeri gambar portfolio (elemen dengan class js-lightbox).
  function openLightbox(src, alt) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox-img').alt = alt || '';
    var lb = document.getElementById('lightbox');
    lb.classList.remove('hidden');
    lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }
  function closeLightbox() {
    var lb = document.getElementById('lightbox');
    lb.classList.add('hidden');
    lb.classList.remove('flex');
    document.body.style.overflow = '';
  }
</script>
</body>
</html>
