@extends('layouts.public')

@section('title', 'B!ng0.dev — Jasa Pengembangan Web & Aplikasi Custom')
@section('description', 'Dari company profile sampai sistem informasi custom — tim B!ng0.dev menangani sisi teknis supaya tim Anda fokus ke pertumbuhan.')

@section('content')
<section class="max-w-6xl mx-auto px-5 md:px-10 pt-16 pb-14 md:pt-28 md:pb-24">
  <p class="font-mono text-xs uppercase tracking-widest text-secondary mb-4">Jasa pengembangan web & aplikasi custom</p>
  <h1 class="font-display font-bold text-3xl md:text-6xl leading-tight text-primary max-w-3xl mb-6">Kami bangun sistem, Anda jalankan bisnis.</h1>
  <p class="text-base md:text-lg text-on-surface-variant max-w-xl mb-8">Dari company profile sampai sistem informasi custom — tim B!ng0.dev menangani sisi teknis supaya tim Anda fokus ke pertumbuhan.</p>
  <div class="flex flex-wrap gap-3">
    <a href="{{ route('requests.create') }}" class="bg-primary text-on-primary px-6 py-3 rounded font-semibold hover:bg-inverse-surface transition-colors">Pesan Jasa</a>
    <a href="{{ route('portfolio.index') }}" class="border border-primary text-primary px-6 py-3 rounded font-semibold hover:bg-surface-container-low transition-colors">Lihat Portfolio</a>
  </div>
</section>


@if ($portfolios->count())
<section class="max-w-6xl mx-auto px-5 md:px-10 py-14 border-t border-outline-variant">
  <div class="flex items-center justify-between mb-8">
    <h2 class="font-display font-bold text-2xl text-primary">Portfolio Pilihan</h2>
    <a href="{{ route('portfolio.index') }}" class="text-sm font-mono text-secondary hover:underline">Lihat semua →</a>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach ($portfolios as $p)
      <div class="fade-in-scroll">@include('portfolio._card', ['p' => $p])</div>
    @endforeach
  </div>
</section>
@endif

@if ($teamMembers->count())
<section class="max-w-6xl mx-auto px-5 md:px-10 py-14 border-t border-outline-variant">
  <h2 class="font-display font-bold text-2xl text-primary mb-8">Tim Kami</h2>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
    @foreach ($teamMembers as $m)
      <div class="fade-in-scroll text-center">
        <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-surface-container-high overflow-hidden mx-auto mb-3 flex items-center justify-center">
          @if ($m->photo)
            <img src="{{ route('media', ['path' => $m->photo]) }}" alt="Foto {{ $m->name }}" class="w-full h-full object-cover" />
          @else
            <span class="material-symbols-outlined text-3xl text-secondary/40">person</span>
          @endif
        </div>
        <div class="font-semibold text-primary text-sm">{{ $m->name }}</div>
        <div class="text-xs text-on-surface-variant mb-2">{{ $m->position }}</div>
        @if ($m->skills && count($m->skills))
          <div class="flex flex-wrap gap-1 justify-center">
            @foreach (array_slice($m->skills, 0, 3) as $s)<span class="px-2 py-0.5 bg-surface-container-low rounded text-[10px] font-mono">{{ $s }}</span>@endforeach
          </div>
        @endif
      </div>
    @endforeach
  </div>
</section>
@endif

<section class="max-w-6xl mx-auto px-5 md:px-10 py-16 border-t border-outline-variant">
  <div class="bg-primary rounded-xl p-8 md:p-14 text-center">
    <h2 class="font-display font-bold text-xl md:text-3xl text-white mb-3">Siap mulai proyek Anda?</h2>
    <p class="text-white/60 mb-6 max-w-md mx-auto">Ceritakan kebutuhan Anda, tim kami akan follow up dalam 1x24 jam kerja.</p>
    <a href="{{ route('requests.create') }}" class="inline-block bg-white text-primary px-6 py-3 rounded font-semibold hover:bg-white/90">Pesan Jasa Sekarang</a>
  </div>
</section>
@endsection
