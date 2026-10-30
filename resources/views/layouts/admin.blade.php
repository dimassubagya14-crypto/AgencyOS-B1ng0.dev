<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>@yield('title', 'Admin') — B!ng0.dev</title>
<meta name="robots" content="noindex, nofollow" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet" />
<link rel="stylesheet" href="/css/app.css" />
@include('partials.pwa-head', ['pwaScope' => 'admin'])
@stack('head')
</head>
<body class="font-body text-on-surface antialiased admin-body">
@include('partials.page-loader', ['loaderMin' => 450, 'loaderMinRepeat' => 150])

<div class="admin-shell min-h-screen bg-surface-container-low">
  <aside class="admin-sidebar bg-primary">
    <div class="admin-sidebar-brand">
      <div class="font-display font-bold text-lg text-white">B!ng0<span class="text-secondary-bright">.dev</span></div>
      <span class="font-mono text-[10px] tracking-widest text-white/50">ADMIN PANEL</span>
    </div>

    <nav class="admin-sidebar-nav">
      @php
        $navItems = [
          ['admin.dashboard', 'space_dashboard', 'Dashboard'],
          ['admin.requests.index', 'inbox', 'Request Jasa'],
          ['admin.clients.index', 'groups', 'Client'],
          ['admin.portfolio.index', 'work', 'Portfolio'],
          ['admin.team.index', 'diversity_3', 'Anggota Tim'],
          ['admin.company-profile.edit', 'apartment', 'Company Profile'],
          ['admin.footer.edit', 'footprint', 'Edit Footer'],
          ['admin.activity-log.index', 'history', 'Activity Log'],
          ['admin.account.edit', 'manage_accounts', 'Akun Admin'],
        ];
      @endphp
      @foreach ($navItems as [$routeName, $icon, $label])
        <a href="{{ route($routeName) }}" class="admin-nav-link {{ request()->routeIs(explode('.index', $routeName)[0].'*') || request()->routeIs($routeName) ? 'is-active' : '' }}">
          <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
          <span>{{ $label }}</span>
        </a>
      @endforeach
    </nav>

    <button type="button" class="theme-toggle admin-theme-toggle" aria-label="Ganti mode tampilan" title="Ganti mode tampilan">
      <span class="material-symbols-outlined theme-icon">dark_mode</span>
      <span>Mode <span class="theme-label">Gelap</span></span>
    </button>

    <div class="admin-sidebar-user">
      <div class="text-white text-sm font-medium">{{ auth()->user()->name }}</div>
      <div class="text-white/50 text-xs font-mono mb-3">{{ auth()->user()->role }}</div>
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="text-xs font-mono text-white/70 hover:text-white flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px]">logout</span> Logout
        </button>
      </form>
    </div>
  </aside>

  <main class="admin-main">
    <header class="md:hidden bg-primary text-white px-4 h-14 flex items-center justify-between sticky top-0 z-30">
      <span class="font-display font-bold">B!ng0<span class="text-secondary-bright">.dev</span></span>
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="text-xs font-mono">Logout</button>
      </form>
    </header>

    <div class="admin-content">
      @if (session('success'))
        <div class="bg-success-container text-success px-4 py-3 rounded-lg text-sm font-medium mb-5">{{ session('success') }}</div>
      @endif
      @if ($errors->any())
        <div class="bg-error-container text-on-error-container px-4 py-3 rounded-lg text-sm font-medium mb-5">
          <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      @yield('content')
    </div>
  </main>

  <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-primary border-t border-white/10 flex items-stretch">
    @php
      $bottomItems = [
        ['admin.dashboard', 'space_dashboard', 'Home'],
        ['admin.requests.index', 'inbox', 'Request'],
        ['admin.clients.index', 'groups', 'Client'],
      ];
    @endphp
    @foreach ($bottomItems as [$routeName, $icon, $label])
      <a href="{{ route($routeName) }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 {{ request()->routeIs($routeName) || request()->routeIs(str($routeName)->before('.index').'.*') ? 'text-secondary-bright' : 'text-white/60' }}">
        <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
        <span class="text-[10px] font-mono">{{ $label }}</span>
      </a>
    @endforeach
    <button type="button" onclick="document.getElementById('admin-more-sheet').classList.toggle('hidden')" class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 text-white/60">
      <span class="material-symbols-outlined text-[22px]">more_horiz</span>
      <span class="text-[10px] font-mono">Lainnya</span>
    </button>
  </nav>

  <div id="admin-more-sheet" class="md:hidden hidden fixed inset-0 z-50 bg-black/40 flex items-end" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white w-full rounded-t-xl p-4">
      <div class="w-10 h-1 bg-outline-variant rounded-full mx-auto mb-4"></div>
      <a href="{{ route('admin.portfolio.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined">work</span>Portfolio</a>
      <a href="{{ route('admin.team.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined">diversity_3</span>Anggota Tim</a>
      <a href="{{ route('admin.company-profile.edit') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined">apartment</span>Company Profile</a>
      <a href="{{ route('admin.footer.edit') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined">footprint</span>Edit Footer</a>
      <button type="button" class="theme-toggle w-full flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-primary hover:bg-surface-container-low">
        <span class="material-symbols-outlined theme-icon">dark_mode</span><span>Mode <span class="theme-label">Gelap</span></span>
      </button>
      <a href="{{ route('admin.activity-log.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined">history</span>Activity Log</a>
      <a href="{{ route('admin.account.edit') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined">manage_accounts</span>Akun Admin</a>
      <div class="border-t border-outline-variant mt-2 pt-2">
        <div class="px-3 py-2 text-xs text-on-surface-variant">{{ auth()->user()->name }} · {{ auth()->user()->role }}</div>
        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <button type="submit" class="w-full flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium text-error hover:bg-error-container/40"><span class="material-symbols-outlined">logout</span>Logout</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="/js/admin.js" defer></script>
<script>
(function(){
  const key="bing0-theme";
  const apply=()=>{ const dark=localStorage.getItem(key)==="dark"; document.documentElement.classList.toggle("dark-theme",dark); document.body.classList.toggle("dark-theme",dark); document.querySelectorAll(".theme-toggle").forEach(b=>{const i=b.querySelector(".theme-icon"),l=b.querySelector(".theme-label"); if(i)i.textContent=dark?"light_mode":"dark_mode"; if(l)l.textContent=dark?"Terang":"Gelap";}); };
  document.addEventListener("DOMContentLoaded",()=>{apply();document.querySelectorAll(".theme-toggle").forEach(b=>b.addEventListener("click",()=>{localStorage.setItem(key,localStorage.getItem(key)==="dark"?"light":"dark");apply();}));});
})();
</script>
@include('partials.pwa-install')
</body>
</html>
