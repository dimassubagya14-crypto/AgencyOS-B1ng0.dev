@extends('layouts.public')

@section('title', 'Portfolio — B!ng0.dev')
@section('description', 'Kumpulan project yang sudah dikerjakan tim B!ng0.dev untuk berbagai klien.')

@push('head')
<link rel="stylesheet" href="/css/portfolio-pro.css" />
<script src="/js/portfolio-pro.js" defer></script>
@endpush

@section('content')
@php
    $techTotal = $portfolios->flatMap(fn ($p) => $p->technologies ?? [])
        ->map(fn ($t) => mb_strtolower(trim((string) $t)))
        ->filter()
        ->unique()
        ->count();
@endphp

<div class="pp">

  <section class="pp-hero">
    <span class="pp-blob b1"></span>
    <span class="pp-blob b2"></span>
    <div class="pp-wrap">
      <p class="pp-kicker" data-reveal>Karya Kami</p>
      <h1 class="pp-title" data-reveal style="--d:80ms">Portfolio</h1>
      <p class="pp-sub" data-reveal style="--d:160ms">Kumpulan proyek yang sudah kami kerjakan untuk klien kami. Cari, saring berdasarkan kategori, lalu klik proyek untuk melihat ceritanya.</p>
      <div class="pp-stats">
        <div class="pp-stat" data-reveal style="--d:0ms"><div class="pp-stat-num" data-count="{{ $portfolios->count() }}">{{ $portfolios->count() }}</div><div class="pp-stat-label">Proyek</div></div>
        <div class="pp-stat" data-reveal style="--d:90ms"><div class="pp-stat-num" data-count="{{ $categories->count() }}">{{ $categories->count() }}</div><div class="pp-stat-label">Kategori</div></div>
        <div class="pp-stat" data-reveal style="--d:180ms"><div class="pp-stat-num" data-count="{{ $techTotal }}">{{ $techTotal }}</div><div class="pp-stat-label">Teknologi</div></div>
      </div>
    </div>
  </section>

  <div class="pp-controls">
    <div class="pp-wrap">
      <div class="pp-bar">
        <label class="pp-search">
          <span class="material-symbols-outlined" aria-hidden="true">search</span>
          <input id="pp-search" type="search" placeholder="Cari proyek atau teknologi…  (tekan /)" autocomplete="off" aria-label="Cari proyek" />
        </label>
        <select id="pp-sort" class="pp-select" aria-label="Urutkan proyek">
          <option value="terbaru">Terbaru</option>
          <option value="tahun">Tahun proyek</option>
          <option value="nama">Nama A–Z</option>
        </select>
      </div>
      <div class="pp-pills" role="group" aria-label="Filter kategori">
        <button type="button" class="pp-pill is-active" data-pp-cat="">Semua</button>
        @foreach ($categories as $c)
          <button type="button" class="pp-pill" data-pp-cat="{{ $c }}">{{ $c }}</button>
        @endforeach
      </div>
    </div>
  </div>

  <section class="pp-wrap pp-main">
    @if ($portfolios->count())
      <p class="pp-count" id="pp-count" aria-live="polite">{{ $portfolios->count() }} proyek</p>
      <div class="pp-grid" id="pp-grid" data-initial="{{ $category }}">
        @foreach ($portfolios as $p)
          <div class="pp-item"
               data-i="{{ $loop->index }}"
               data-cat="{{ $p->category }}"
               data-year="{{ (int) $p->project_year }}"
               data-name="{{ $p->name }}"
               data-text="{{ mb_strtolower($p->name.' '.$p->category.' '.$p->tagline.' '.$p->description.' '.implode(' ', $p->technologies ?? [])) }}">
            @include('portfolio._card', ['p' => $p])
          </div>
        @endforeach
      </div>
      <div class="pp-empty" id="pp-empty" hidden>
        <p style="margin:0 0 .5rem">Tidak ada proyek yang cocok dengan pencarianmu.</p>
        <button type="button" class="pp-link" data-pp-reset>Reset filter</button>
      </div>
    @else
      <p class="pp-count">Belum ada portfolio yang dipublikasikan.</p>
    @endif
  </section>

</div>
@endsection
