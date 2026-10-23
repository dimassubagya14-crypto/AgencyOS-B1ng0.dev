@extends('layouts.public')

@section('title', $portfolio->name.' — Portfolio B!ng0.dev')
@section('description', Str::limit(strip_tags($portfolio->tagline ?: $portfolio->description), 150))

@section('content')

@if ($portfolio->banner)
  <div class="w-full h-56 md:h-96 bg-surface-container-high overflow-hidden">
    <img src="{{ route('media', ['path' => $portfolio->banner]) }}" alt="Banner project {{ $portfolio->name }}" class="w-full h-full object-cover" />
  </div>
@endif

<article class="max-w-3xl mx-auto px-5 md:px-10 py-10 md:py-14">
  <a href="{{ route('portfolio.index') }}" class="text-xs font-mono text-secondary hover:underline mb-6 inline-block">← Kembali ke Portfolio</a>

  <div class="flex flex-wrap items-center gap-2 mb-3">
    <span class="font-mono text-[11px] uppercase tracking-wider text-secondary">{{ $portfolio->category }}</span>
    @if ($portfolio->project_year)<span class="text-on-surface-variant text-xs">· {{ $portfolio->project_year }}</span>@endif
    @if ($portfolio->duration)<span class="text-on-surface-variant text-xs">· {{ $portfolio->duration }}</span>@endif
  </div>

  <h1 class="font-display font-bold text-2xl md:text-4xl text-primary mb-2">{{ $portfolio->name }}</h1>
  @if ($portfolio->tagline)
    <p class="text-base md:text-lg text-on-surface-variant mb-4">{{ $portfolio->tagline }}</p>
  @endif
  @if ($portfolio->client_name)
    <p class="text-sm text-on-surface-variant mb-8">Client: <span class="font-medium text-primary">{{ $portfolio->client_name }}</span></p>
  @endif

  @if (!$portfolio->banner && $portfolio->thumbnail)
    <img src="{{ route('media', ['path' => $portfolio->thumbnail]) }}" alt="Tampilan {{ $portfolio->name }}" class="w-full rounded-lg mb-8 border border-outline-variant" />
  @endif

  <p class="text-sm md:text-base mb-8 leading-relaxed">{{ $portfolio->description }}</p>

  @if ($portfolio->problem || $portfolio->solution)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      @if ($portfolio->problem)
        <div class="bg-surface-container-low rounded-lg p-5">
          <h2 class="font-mono text-xs uppercase tracking-wider text-on-surface-variant mb-2">Masalah</h2>
          <p class="text-sm">{{ $portfolio->problem }}</p>
        </div>
      @endif
      @if ($portfolio->solution)
        <div class="bg-surface-container-low rounded-lg p-5">
          <h2 class="font-mono text-xs uppercase tracking-wider text-on-surface-variant mb-2">Solusi</h2>
          <p class="text-sm">{{ $portfolio->solution }}</p>
        </div>
      @endif
    </div>
  @endif

  @if ($portfolio->results)
    <div class="bg-primary rounded-lg p-6 mb-8">
      <h2 class="font-mono text-xs uppercase tracking-wider text-white/50 mb-2">Hasil</h2>
      <p class="text-sm text-white">{{ $portfolio->results }}</p>
    </div>
  @endif

  @if ($portfolio->gallery && count($portfolio->gallery))
    <div class="mb-8">
      <h2 class="font-mono text-xs uppercase tracking-wider text-on-surface-variant mb-3">Galeri</h2>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        @foreach ($portfolio->gallery as $img)
          <img src="{{ route('media', ['path' => $img]) }}" alt="Screenshot {{ $portfolio->name }}" onclick="openLightbox('{{ route('media', ['path' => $img]) }}', 'Screenshot {{ $portfolio->name }}')" class="w-full h-32 md:h-40 object-cover rounded-lg border border-outline-variant cursor-pointer hover:opacity-90 transition-opacity" loading="lazy" />
        @endforeach
      </div>
    </div>
  @endif

  @if ($portfolio->features && count($portfolio->features))
    <div class="mb-6">
      <h2 class="font-mono text-xs uppercase tracking-wider text-on-surface-variant mb-2">Fitur</h2>
      <div class="flex flex-wrap gap-2">
        @foreach ($portfolio->features as $f)<span class="px-2 py-1 bg-surface-container-low rounded text-xs">{{ $f }}</span>@endforeach
      </div>
    </div>
  @endif

  @if ($portfolio->technologies && count($portfolio->technologies))
    <div class="mb-8">
      <h2 class="font-mono text-xs uppercase tracking-wider text-on-surface-variant mb-2">Teknologi</h2>
      <div class="flex flex-wrap gap-2">
        @foreach ($portfolio->technologies as $t)<span class="px-2 py-1 bg-surface-container-high rounded text-xs font-mono">{{ $t }}</span>@endforeach
      </div>
    </div>
  @endif

  @if ($portfolio->testimonial_text)
    <blockquote class="border-l-4 border-secondary-bright pl-5 py-1 mb-8">
      <p class="text-sm md:text-base italic text-primary mb-2">&ldquo;{{ $portfolio->testimonial_text }}&rdquo;</p>
      @if ($portfolio->testimonial_author)
        <cite class="text-xs font-mono text-on-surface-variant not-italic">— {{ $portfolio->testimonial_author }}</cite>
      @endif
    </blockquote>
  @endif

  <div class="flex flex-wrap items-center gap-3 mb-10">
    @if ($portfolio->url)
      <a href="{{ $portfolio->url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-outline-variant px-4 py-2 text-sm font-medium text-secondary hover:bg-surface-container-low">Kunjungi project <span class="material-symbols-outlined text-[16px]">open_in_new</span></a>
    @endif
  </div>

  <div class="border-t border-outline-variant pt-8 text-center">
    <p class="text-sm text-on-surface-variant mb-4">Tertarik dengan hasil serupa untuk bisnis Anda?</p>
    <a href="{{ route('requests.create') }}" class="inline-block bg-primary text-on-primary px-6 py-3 rounded font-semibold hover:bg-inverse-surface">Pesan Jasa Sekarang</a>
  </div>
</article>

@if ($related->count())
<section class="max-w-6xl mx-auto px-5 md:px-10 pb-16 border-t border-outline-variant pt-12">
  <h2 class="font-display font-bold text-xl text-primary mb-6">Project Serupa</h2>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach ($related as $p)
      @include('portfolio._card', ['p' => $p])
    @endforeach
  </div>
</section>
@endif
@endsection
