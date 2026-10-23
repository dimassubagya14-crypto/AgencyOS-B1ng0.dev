<a href="{{ route('portfolio.show', $p->slug) }}" class="text-left border border-outline-variant rounded-lg overflow-hidden card-shadow hover:border-secondary-bright transition-colors bg-white block">
  <div class="h-36 bg-surface-container-high flex items-center justify-center overflow-hidden">
    @if ($p->thumbnail)
      <img src="{{ route('media', ['path' => $p->thumbnail]) }}" alt="Tampilan project {{ $p->name }}" class="w-full h-full object-cover" loading="lazy" />
    @else
      <span class="material-symbols-outlined text-4xl text-secondary/40">web</span>
    @endif
  </div>
  <div class="p-5">
    <span class="font-mono text-[11px] uppercase tracking-wider text-secondary">{{ $p->category }}</span>
    <h3 class="font-semibold text-primary mt-1 mb-1">{{ $p->name }}</h3>
    <p class="text-sm text-on-surface-variant line-clamp-2">{{ $p->description }}</p>
  </div>
</a>
