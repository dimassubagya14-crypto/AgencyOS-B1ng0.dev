@extends('layouts.public')

@section('title', $member->name.' — Portofolio')
@section('description', Str::limit(strip_tags($member->headline ?: $member->about ?: $member->bio ?: 'Portofolio '.$member->name), 150))

@push('head')
<link rel="stylesheet" href="/css/portfolio-pribadi.css" />
@php
    $ld = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $member->name,
        'jobTitle' => $member->headline ?: $member->position,
        'description' => Str::limit(strip_tags((string) ($member->about ?: $member->bio)), 200),
        'url' => $member->portfolioUrl(),
        'image' => $member->photo ? route('media', ['path' => $member->photo]) : null,
        'worksFor' => ['@type' => 'Organization', 'name' => 'B!ng0.dev'],
        'sameAs' => array_values(array_filter($member->social_links ?? [])) ?: null,
    ]);
@endphp
@if ($member->photo)<meta property="og:image" content="{{ route('media', ['path' => $member->photo]) }}" />@endif
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
@php
    $norm = fn ($v) => mb_strtolower(trim((string) $v));
    $agencyProjects = $member->agencyProjects();
    $projects = array_values($member->projects ?? []);
    $certificates = array_values($member->certificates ?? []);
    $social = $member->social_links ?? [];
    $skills = $member->skills ?? [];
    $agencyTech = $agencyProjects->flatMap(fn ($p) => $p->technologies ?? [])->all();
    $allSkills = collect($skills)->merge($agencyTech)->filter(fn ($t) => $norm($t) !== '')->unique(fn ($t) => $norm($t))->values();
    $socialLabels = ['github' => 'GitHub', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'website' => 'Website'];
    $email = $member->show_contact ? $member->contactEmail() : null;
    $wa = null;
    if ($member->show_contact && $member->phone) {
        $wa = preg_replace('/\D+/', '', (string) $member->phone);
        if (str_starts_with($wa, '0')) {
            $wa = '62'.substr($wa, 1);
        }
    }
    $headline = $member->headline ?: $member->position;
    $about = $member->about ?: $member->bio;
    $totalProjects = $agencyProjects->count() + count($projects);
    $typedWords = $allSkills->take(6)->values()->all();
    $hasBoth = $agencyProjects->count() > 0 && count($projects) > 0;
    $experiences = array_values($member->experiences ?? []);
    $education = array_values($member->education ?? []);
    $languages = array_values($member->languages ?? []);
    $cvUrl = (! empty($preview) || ! $member->slug)
        ? route('admin.team.portfolio.cv', $member)
        : route('team.cv', $member->slug);
    $nav = [];
    if ($about) { $nav['tentang'] = 'Tentang'; }
    if (count($experiences)) { $nav['pengalaman'] = 'Pengalaman'; }
    if ($allSkills->count()) { $nav['keahlian'] = 'Keahlian'; }
    if ($totalProjects) { $nav['proyek'] = 'Proyek'; }
    if (count($education)) { $nav['pendidikan'] = 'Pendidikan'; }
    if (count($certificates)) { $nav['sertifikat'] = 'Sertifikat'; }
@endphp

<div class="pf">

@if (! empty($preview))
  <div class="pf-wrap">
    <div class="pf-preview">
      Mode pratinjau — halaman ini {{ $member->portfolio_published ? 'sudah' : 'belum' }} dipublikasikan.
      <a href="{{ route('admin.team.portfolio.edit', $member) }}">Kembali ke editor</a>
    </div>
  </div>
@endif

{{-- HERO --}}
<section class="pf-hero">
  <span class="pf-blob b1"></span>
  <span class="pf-blob b2"></span>
  <div class="pf-wrap">
    <div class="pf-hero-in">
      <div class="pf-avatar" data-reveal>
        <div class="pf-avatar-in">
          @if ($member->photo)
            <img src="{{ route('media', ['path' => $member->photo]) }}" alt="Foto {{ $member->name }}" />
          @else
            <span class="material-symbols-outlined" style="font-size:3.5rem;opacity:.4">person</span>
          @endif
        </div>
      </div>
      <div>
        <h1 class="pf-name" data-reveal style="--d:80ms">{{ $member->name }}</h1>
        @if ($headline)<p class="pf-headline" data-reveal style="--d:160ms">{{ $headline }}</p>@endif
        @if ($member->location)<p class="pf-headline" style="margin-top:.25rem;font-size:.9rem" data-reveal style="--d:200ms">{{ $member->location }}</p>@endif
        @if ($member->open_to_work)
          <p class="pf-open" data-reveal style="--d:220ms"><i></i> Terbuka untuk pekerjaan @if ($member->desired_role) · {{ $member->desired_role }}@endif</p>
        @endif
        @if (count($typedWords))
          <p class="pf-typed" data-reveal style="--d:240ms">&gt; <span data-typewriter="{{ json_encode($typedWords, JSON_UNESCAPED_UNICODE) }}">{{ $typedWords[0] }}</span><i class="pf-caret"></i></p>
        @endif
        <div class="pf-links" data-reveal style="--d:300ms">
          <a href="{{ $cvUrl }}" target="_blank" class="pf-act">Unduh CV</a>
          @foreach ($socialLabels as $key => $label)
            @if (! empty($social[$key]))
              <a href="{{ $social[$key] }}" target="_blank" rel="noopener noreferrer" class="pf-pill">{{ $label }}</a>
            @endif
          @endforeach
          @if ($email)<a href="mailto:{{ $email }}" class="pf-pill">Email</a>@endif
          @if ($wa)<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener noreferrer" class="pf-pill">WhatsApp</a>@endif
        </div>
      </div>
    </div>

    <div class="pf-stats">
      <div class="pf-stat" data-reveal style="--d:0ms"><div class="pf-stat-num" data-count="{{ $totalProjects }}">{{ $totalProjects }}</div><div class="pf-stat-label">Proyek</div></div>
      <div class="pf-stat" data-reveal style="--d:90ms"><div class="pf-stat-num" data-count="{{ $allSkills->count() }}">{{ $allSkills->count() }}</div><div class="pf-stat-label">Keahlian</div></div>
      <div class="pf-stat" data-reveal style="--d:180ms"><div class="pf-stat-num" data-count="{{ count($certificates) }}">{{ count($certificates) }}</div><div class="pf-stat-label">Sertifikat</div></div>
    </div>
  </div>
</section>

{{-- NAV STICKY --}}
@if (count($nav) > 1)
  <div class="pf-nav">
    <div class="pf-wrap">
      <div class="pf-nav-in">
        @foreach ($nav as $id => $label)
          <a href="#{{ $id }}" data-spy>{{ $label }}</a>
        @endforeach
      </div>
    </div>
  </div>
@endif

<div class="pf-wrap">

  @if ($about)
    <section id="tentang" class="pf-section">
      <h2 class="pf-title" data-reveal>Tentang</h2>
      <p class="pf-about" data-reveal style="--d:80ms">{!! nl2br(e($about)) !!}</p>
    </section>
  @endif

  @if (count($experiences))
    <section id="pengalaman" class="pf-section">
      <h2 class="pf-title" data-reveal>Pengalaman</h2>
      <div class="pf-tl">
        @foreach ($experiences as $e)
          <div data-reveal style="--d:{{ min($loop->index, 6) * 80 }}ms">
            <div class="pf-tl-item">
              <div class="pf-tl-name">{{ $e['role'] }}@if (! empty($e['company'])) · {{ $e['company'] }}@endif</div>
              @if (! empty($e['period']))<div class="pf-tl-sub">{{ $e['period'] }}</div>@endif
              @if (! empty($e['description']))<div class="pf-tl-desc">{{ $e['description'] }}</div>@endif
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endif

  @if ($allSkills->count())
    <section id="keahlian" class="pf-section">
      <h2 class="pf-title" data-reveal>Keahlian</h2>
      <div class="pf-chips">
        @foreach ($allSkills as $t)
          <span data-reveal style="--d:{{ min($loop->index, 14) * 45 }}ms"><button type="button" class="pf-chip" data-skill="{{ $norm($t) }}">{{ $t }}</button></span>
        @endforeach
      </div>
      @if ($totalProjects)<p class="pf-hint">Klik sebuah keahlian untuk melihat proyek yang memakainya.</p>@endif
    </section>
  @endif

  @if ($totalProjects)
    <section id="proyek" class="pf-section">
      <h2 class="pf-title" data-reveal>Proyek</h2>

      @if ($hasBoth)
        <div class="pf-tabs" data-reveal>
          <button type="button" class="pf-tab is-active" data-filter-kind="all">Semua ({{ $totalProjects }})</button>
          <button type="button" class="pf-tab" data-filter-kind="agency">Proyek Agency ({{ $agencyProjects->count() }})</button>
          <button type="button" class="pf-tab" data-filter-kind="personal">Proyek Pribadi ({{ count($projects) }})</button>
        </div>
      @endif

      <p id="pf-status" class="pf-status" hidden><span id="pf-status-text"></span><button type="button" class="pf-reset" data-filter-reset>Hapus filter</button></p>

      <div id="pf-grid" class="pf-grid">
        @foreach ($agencyProjects as $p)
          @php
            $techAttr = collect($p->technologies ?? [])->map(fn ($t) => str_replace('|', '', $norm($t)))->filter()->unique()->implode('|');
          @endphp
          <div class="pf-item" data-kind="agency" data-tech="{{ $techAttr }}" data-reveal style="--d:{{ ($loop->index % 4) * 80 }}ms">
            @include('portfolio._card', ['p' => $p, 'noReveal' => true])
          </div>
        @endforeach

        @foreach ($projects as $pr)
          @php
            $prTags = collect(explode(',', (string) ($pr['tech'] ?? '')))->map(fn ($t) => trim($t))->filter()->values();
            $prTech = $prTags->map(fn ($t) => str_replace('|', '', $norm($t)))->filter()->unique()->implode('|');
            $hue = crc32((string) $pr['title']) % 360;
          @endphp
          <div class="pf-item" data-kind="personal" data-tech="{{ $prTech }}" data-reveal style="--d:{{ ($loop->index % 4) * 80 }}ms">
            <article class="pf-card" data-tilt>
              <div class="pf-card-top" style="--h:{{ $hue }}"><span>{{ mb_strtoupper(mb_substr((string) $pr['title'], 0, 1)) }}</span></div>
              <div class="pf-card-body">
                <div class="pf-card-meta">Proyek pribadi @if (! empty($pr['year'])) · {{ $pr['year'] }}@endif</div>
                <h3>{{ $pr['title'] }}</h3>
                @if (! empty($pr['description']))<p>{{ $pr['description'] }}</p>@endif
                @if ($prTags->count())
                  <div class="pf-tags">@foreach ($prTags as $t)<span>{{ $t }}</span>@endforeach</div>
                @endif
                @if (! empty($pr['url']))<a href="{{ $pr['url'] }}" target="_blank" rel="noopener noreferrer" class="pf-link">Lihat proyek →</a>@endif
              </div>
            </article>
          </div>
        @endforeach
      </div>

      <p id="pf-empty" class="pf-empty" hidden>Belum ada proyek untuk filter ini. <button type="button" class="pf-reset" data-filter-reset>Tampilkan semua</button></p>
    </section>
  @endif

  @if (count($education))
    <section id="pendidikan" class="pf-section">
      <h2 class="pf-title" data-reveal>Pendidikan</h2>
      <div class="pf-tl">
        @foreach ($education as $ed)
          <div data-reveal style="--d:{{ min($loop->index, 6) * 80 }}ms">
            <div class="pf-tl-item">
              <div class="pf-tl-name">{{ $ed['school'] }}</div>
              <div class="pf-tl-sub">{{ $ed['degree'] ?? '' }}@if (! empty($ed['degree']) && ! empty($ed['period'])) · @endif{{ $ed['period'] ?? '' }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endif

  @if (count($certificates))
    <section id="sertifikat" class="pf-section">
      <h2 class="pf-title" data-reveal>Sertifikat</h2>
      <div class="pf-tl">
        @foreach ($certificates as $c)
          <div data-reveal style="--d:{{ min($loop->index, 6) * 80 }}ms">
            <div class="pf-tl-item">
              <div class="pf-tl-name">{{ $c['name'] }}</div>
              <div class="pf-tl-sub">{{ $c['issuer'] ?? '' }}@if (! empty($c['issuer']) && ! empty($c['year'])) · @endif{{ $c['year'] ?? '' }}</div>
              @if (! empty($c['url']))<a href="{{ $c['url'] }}" target="_blank" rel="noopener noreferrer" class="pf-link" style="display:inline-block;margin-top:.35rem">Lihat bukti →</a>@endif
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endif

  @if (count($languages))
    <section id="bahasa" class="pf-section">
      <h2 class="pf-title" data-reveal>Bahasa</h2>
      <div class="pf-langs">
        @foreach ($languages as $l)
          <span data-reveal style="--d:{{ min($loop->index, 8) * 60 }}ms"><span class="pf-lang">{{ $l }}</span></span>
        @endforeach
      </div>
    </section>
  @endif

  <div class="pf-cta" data-reveal>
    <h3>Butuh website untuk bisnis Anda?</h3>
    <p>{{ $member->name }} bekerja di B!ng0.dev. Ceritakan kebutuhan Anda, tim kami siap membantu.</p>
    <a href="{{ route('requests.create') }}" class="pf-btn">Pesan Jasa</a>
  </div>

</div>
</div>
@endsection
