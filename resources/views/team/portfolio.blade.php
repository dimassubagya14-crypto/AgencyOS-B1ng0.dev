@extends('layouts.public')

@section('title', $member->name.' — Portofolio')
@section('description', Str::limit(strip_tags($member->headline ?: $member->about ?: $member->bio ?: 'Portofolio '.$member->name), 150))

@push('head')
<style>
  .tp-wrap{max-width:56rem;margin-left:auto;margin-right:auto;padding-left:1.25rem;padding-right:1.25rem}
  @media (min-width:768px){.tp-wrap{padding-left:2.5rem;padding-right:2.5rem}}
  .tp-hero{display:flex;flex-direction:column;gap:1.5rem;padding-top:2.5rem}
  @media (min-width:640px){.tp-hero{flex-direction:row;align-items:center}}
  @media (min-width:768px){.tp-hero{padding-top:4rem}}
  .tp-avatar{width:7rem;height:7rem}
  @media (min-width:768px){.tp-avatar{width:9rem;height:9rem}}
  .tp-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:1.25rem}
  @media (min-width:640px){.tp-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  .tp-stack>*+*{margin-top:.75rem}
  .tp-ul{text-decoration:underline}
  .tp-icon-lg{font-size:3rem}
</style>
@endpush

@section('content')
@php
    $agencyProjects = $member->agencyProjects();
    $projects = $member->projects ?? [];
    $certificates = $member->certificates ?? [];
    $social = $member->social_links ?? [];
    $skills = $member->skills ?? [];
    $agencyTech = $agencyProjects->flatMap(fn ($p) => $p->technologies ?? [])->unique()->values();
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
@endphp

@if (! empty($preview))
  <div class="tp-wrap mt-4">
    <div class="px-4 py-3 rounded-lg text-sm font-medium bg-surface-container-high text-primary">
      Mode pratinjau — halaman ini {{ $member->portfolio_published ? 'sudah' : 'belum' }} dipublikasikan.
      <a href="{{ route('admin.team.portfolio.edit', $member) }}" class="tp-ul">Kembali ke editor</a>
    </div>
  </div>
@endif

<section class="tp-wrap">
  <div class="tp-hero">
    <div class="tp-avatar rounded-full bg-surface-container-high overflow-hidden flex items-center justify-center flex-shrink-0">
      @if ($member->photo)
        <img src="{{ route('media', ['path' => $member->photo]) }}" alt="Foto {{ $member->name }}" class="w-full h-full object-cover" />
      @else
        <span class="material-symbols-outlined tp-icon-lg text-secondary/40">person</span>
      @endif
    </div>
    <div>
      <h1 class="font-display font-bold text-3xl md:text-4xl text-primary">{{ $member->name }}</h1>
      @if ($headline)<p class="text-base md:text-lg text-on-surface-variant mt-1">{{ $headline }}</p>@endif
      @if ($member->location)<p class="text-sm text-on-surface-variant mt-1">{{ $member->location }}</p>@endif
      <div class="flex flex-wrap gap-2 mt-4">
        @foreach ($socialLabels as $key => $label)
          @if (! empty($social[$key]))
            <a href="{{ $social[$key] }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 border border-outline-variant rounded text-xs font-mono hover:border-secondary-bright">{{ $label }}</a>
          @endif
        @endforeach
        @if ($email)<a href="mailto:{{ $email }}" class="px-3 py-1.5 border border-outline-variant rounded text-xs font-mono hover:border-secondary-bright">Email</a>@endif
        @if ($wa)<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 border border-outline-variant rounded text-xs font-mono hover:border-secondary-bright">WhatsApp</a>@endif
      </div>
    </div>
  </div>
</section>

@if ($about)
  <section class="tp-wrap pt-12">
    <h2 class="font-mono text-xs uppercase tracking-wider text-secondary mb-3">Tentang</h2>
    <p class="text-base text-on-surface leading-relaxed">{!! nl2br(e($about)) !!}</p>
  </section>
@endif

@if (count($skills) || $agencyTech->count())
  <section class="tp-wrap pt-12">
    <h2 class="font-mono text-xs uppercase tracking-wider text-secondary mb-3">Keahlian</h2>
    <div class="flex flex-wrap gap-2">
      @foreach ($skills as $s)
        <span class="px-3 py-1 bg-surface-container-low rounded text-xs font-mono">{{ $s }}</span>
      @endforeach
      @foreach ($agencyTech as $t)
        @unless (in_array(strtolower($t), array_map('strtolower', $skills), true))
          <span class="px-3 py-1 bg-surface-container-low rounded text-xs font-mono text-on-surface-variant">{{ $t }}</span>
        @endunless
      @endforeach
    </div>
  </section>
@endif

@if ($agencyProjects->count())
  <section class="tp-wrap pt-12">
    <h2 class="font-mono text-xs uppercase tracking-wider text-secondary mb-1">Proyek di B!ng0.dev</h2>
    <p class="text-sm text-on-surface-variant mb-4">Proyek yang dikerjakan bersama agency.</p>
    <div class="tp-grid">
      @foreach ($agencyProjects as $p)
        @include('portfolio._card', ['p' => $p])
      @endforeach
    </div>
  </section>
@endif

@if (count($projects))
  <section class="tp-wrap pt-12">
    <h2 class="font-mono text-xs uppercase tracking-wider text-secondary mb-4">Proyek Pribadi</h2>
    <div class="tp-grid">
      @foreach ($projects as $pr)
        <div class="border border-outline-variant rounded-lg p-5 card-shadow bg-white">
          <div class="flex items-center gap-2 mb-1">
            <h3 class="font-semibold text-primary">{{ $pr['title'] }}</h3>
            @if (! empty($pr['year']))<span class="text-xs text-on-surface-variant">· {{ $pr['year'] }}</span>@endif
          </div>
          @if (! empty($pr['description']))<p class="text-sm text-on-surface-variant mb-3">{{ $pr['description'] }}</p>@endif
          @if (! empty($pr['tech']))
            <div class="flex flex-wrap gap-1.5 mb-3">
              @foreach (array_filter(array_map('trim', explode(',', $pr['tech']))) as $t)
                <span class="px-2 py-0.5 bg-surface-container-low rounded text-[11px] font-mono">{{ $t }}</span>
              @endforeach
            </div>
          @endif
          @if (! empty($pr['url']))<a href="{{ $pr['url'] }}" target="_blank" rel="noopener noreferrer" class="text-sm text-secondary hover:underline">Lihat proyek →</a>@endif
        </div>
      @endforeach
    </div>
  </section>
@endif

@if (count($certificates))
  <section class="tp-wrap pt-12">
    <h2 class="font-mono text-xs uppercase tracking-wider text-secondary mb-4">Sertifikat</h2>
    <div class="tp-stack">
      @foreach ($certificates as $c)
        <div class="border border-outline-variant rounded-lg p-4 bg-white">
          <div class="font-semibold text-primary text-sm">{{ $c['name'] }}</div>
          <div class="text-xs text-on-surface-variant mt-0.5">
            {{ $c['issuer'] ?? '' }}@if (! empty($c['issuer']) && ! empty($c['year'])) · @endif{{ $c['year'] ?? '' }}
          </div>
          @if (! empty($c['url']))<a href="{{ $c['url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-secondary hover:underline mt-1 inline-block">Lihat bukti →</a>@endif
        </div>
      @endforeach
    </div>
  </section>
@endif

<section class="tp-wrap pt-14">
  <div class="border border-outline-variant rounded-lg p-6 bg-surface-container-low">
    <div class="font-semibold text-primary mb-1">Butuh website untuk bisnis Anda?</div>
    <p class="text-sm text-on-surface-variant mb-4">{{ $member->name }} bekerja di B!ng0.dev. Ceritakan kebutuhan Anda, tim kami siap membantu.</p>
    <a href="{{ route('requests.create') }}" class="inline-block bg-primary text-on-primary px-4 py-2 rounded text-sm font-semibold">Pesan Jasa</a>
  </div>
</section>
@endsection
