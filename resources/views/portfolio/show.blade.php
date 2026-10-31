@extends('layouts.public')

@section('title', $portfolio->name.' — Portfolio B!ng0.dev')
@section('description', Str::limit(strip_tags($portfolio->tagline ?: $portfolio->description), 150))

@push('head')
<link rel="stylesheet" href="/css/portfolio-pro.css" />
<script src="/js/portfolio-pro.js" defer></script>
@endpush

@section('content')
@php
    $heroImg = $portfolio->banner ?: $portfolio->thumbnail;
    $gallery = array_values($portfolio->gallery ?? []);
    $features = array_values($portfolio->features ?? []);
    $techs = array_values($portfolio->technologies ?? []);
    $meta = array_filter([
        'Klien' => $portfolio->client_name,
        'Tahun' => $portfolio->project_year,
        'Durasi' => $portfolio->duration,
        'Kategori' => $portfolio->category,
    ], fn ($v) => filled($v));
    $nav = ['ringkasan' => 'Ringkasan'];
    if ($portfolio->problem || $portfolio->solution) { $nav['masalah-solusi'] = 'Masalah & Solusi'; }
    if ($portfolio->results) { $nav['hasil'] = 'Hasil'; }
    if (count($gallery)) { $nav['galeri'] = 'Galeri'; }
    if (count($features) || count($techs)) { $nav['teknis'] = 'Fitur & Teknologi'; }
    if ($portfolio->testimonial_text) { $nav['testimoni'] = 'Testimoni'; }
@endphp

<div class="pp">

  <header class="pp-dhero {{ $heroImg ? '' : 'no-img' }}">
    @if ($heroImg)
      <img class="pp-dhero-img" src="{{ route('media', ['path' => $heroImg]) }}" alt="Tampilan project {{ $portfolio->name }}" />
    @else
      <span class="pp-blob b1"></span>
      <span class="pp-blob b2"></span>
    @endif
    <div class="pp-wrap pp-dhero-in">
      <a href="{{ route('portfolio.index') }}" class="pp-back">← Semua Portfolio</a>
      <div class="pp-tags">
        <span class="pp-tag">{{ $portfolio->category }}</span>
        @if ($portfolio->project_year)<span class="pp-tag">{{ $portfolio->project_year }}</span>@endif
      </div>
      <h1 class="pp-dtitle">{{ $portfolio->name }}</h1>
      @if ($portfolio->tagline)<p class="pp-dsub">{{ $portfolio->tagline }}</p>@endif
      <div class="pp-actions">
        @if ($portfolio->url)
          <a href="{{ $portfolio->url }}" target="_blank" rel="noopener noreferrer" class="pp-btn">Kunjungi Website →</a>
        @endif
        <button type="button" class="pp-btn pp-btn-ghost" data-pp-share><span data-label>Bagikan</span></button>
      </div>
    </div>
  </header>

  <div class="pp-wrap">

    @if (count($meta))
      <div class="pp-meta">
        @foreach ($meta as $label => $value)
          <div class="pp-meta-item" data-reveal style="--d:{{ $loop->index * 80 }}ms">
            <div class="pp-meta-label">{{ $label }}</div>
            <div class="pp-meta-value">{{ $value }}</div>
          </div>
        @endforeach
      </div>
    @endif

  </div>

  @if (count($nav) > 2)
    <nav class="pp-nav" aria-label="Bagian halaman">
      <div class="pp-wrap">
        <div class="pp-nav-in">
          @foreach ($nav as $id => $label)
            <a href="#{{ $id }}" data-spy>{{ $label }}</a>
          @endforeach
        </div>
      </div>
    </nav>
  @endif

  <div class="pp-wrap">
    <div class="pp-body">

      <section id="ringkasan" class="pp-section">
        <h2 class="pp-h" data-reveal>Ringkasan</h2>
        <p class="pp-lead" data-reveal style="--d:80ms">{{ $portfolio->description }}</p>
      </section>

      @if ($portfolio->problem || $portfolio->solution)
        <section id="masalah-solusi" class="pp-section">
          <h2 class="pp-h" data-reveal>Masalah &amp; Solusi</h2>
          <div class="pp-duo">
            @if ($portfolio->problem)
              <div data-reveal>
                <div class="pp-card">
                  <h3>Masalah</h3>
                  <p>{{ $portfolio->problem }}</p>
                </div>
              </div>
            @endif
            @if ($portfolio->solution)
              <div data-reveal style="--d:100ms">
                <div class="pp-card">
                  <h3>Solusi</h3>
                  <p>{{ $portfolio->solution }}</p>
                </div>
              </div>
            @endif
          </div>
        </section>
      @endif

      @if ($portfolio->results)
        <section id="hasil" class="pp-section">
          <h2 class="pp-h" data-reveal>Hasil</h2>
          <div class="pp-result" data-reveal style="--d:80ms"><p>{{ $portfolio->results }}</p></div>
        </section>
      @endif

      @if (count($gallery))
        <section id="galeri" class="pp-section">
          <h2 class="pp-h" data-reveal>Galeri</h2>
          <div class="pp-gallery">
            @foreach ($gallery as $img)
              <button type="button" class="pp-shot" data-pp-full="{{ route('media', ['path' => $img]) }}" data-alt="Screenshot {{ $portfolio->name }}" aria-label="Perbesar gambar {{ $loop->iteration }}" data-reveal style="--d:{{ min($loop->index, 8) * 70 }}ms">
                <img src="{{ route('media', ['path' => $img]) }}" alt="Screenshot {{ $portfolio->name }}" loading="lazy" />
              </button>
            @endforeach
          </div>
        </section>
      @endif

      @if (count($features) || count($techs))
        <section id="teknis" class="pp-section">
          <h2 class="pp-h" data-reveal>Fitur &amp; Teknologi</h2>
          @if (count($features))
            <ul class="pp-feats">
              @foreach ($features as $f)
                <li data-reveal style="--d:{{ min($loop->index, 10) * 50 }}ms"><span class="pp-feat">{{ $f }}</span></li>
              @endforeach
            </ul>
          @endif
          @if (count($techs))
            <p class="pp-sub-h" data-reveal>Teknologi yang dipakai</p>
            <div class="pp-chips">
              @foreach ($techs as $t)
                <span data-reveal style="--d:{{ min($loop->index, 12) * 40 }}ms"><span class="pp-chip">{{ $t }}</span></span>
              @endforeach
            </div>
          @endif
        </section>
      @endif

      @if ($portfolio->testimonial_text)
        <section id="testimoni" class="pp-section">
          <h2 class="pp-h" data-reveal>Testimoni</h2>
          <blockquote class="pp-quote" data-reveal style="--d:80ms">
            <p>&ldquo;{{ $portfolio->testimonial_text }}&rdquo;</p>
            @if ($portfolio->testimonial_author)<cite>— {{ $portfolio->testimonial_author }}</cite>@endif
          </blockquote>
        </section>
      @endif

      <div class="pp-cta" data-reveal>
        <h2>Tertarik dengan hasil serupa?</h2>
        <p>Ceritakan kebutuhan Anda, tim kami siap membantu mewujudkannya.</p>
        <a href="{{ route('requests.create') }}" class="pp-btn">Pesan Jasa Sekarang</a>
      </div>

      @if ($prev || $next)
        <div class="pp-pn">
          @if ($prev)
            <div data-reveal>
              <a href="{{ route('portfolio.show', $prev->slug) }}">
                <small>← Sebelumnya</small>
                <strong>{{ $prev->name }}</strong>
              </a>
            </div>
          @endif
          @if ($next)
            <div data-reveal style="--d:80ms">
              <a href="{{ route('portfolio.show', $next->slug) }}" class="is-next">
                <small>Berikutnya →</small>
                <strong>{{ $next->name }}</strong>
              </a>
            </div>
          @endif
        </div>
      @endif

    </div>
  </div>

  @if ($related->count())
    <section class="pp-wrap pp-related">
      <h2 class="pp-h" data-reveal>Project Serupa</h2>
      <div class="pp-grid">
        @foreach ($related as $p)
          <div class="pp-item">@include('portfolio._card', ['p' => $p])</div>
        @endforeach
      </div>
    </section>
  @endif

</div>
@endsection
