@extends('layouts.admin')

@section('title', 'Portofolio Pribadi — '.$member->name)

@section('content')
<style>
  .ap-break{word-break:ap-break}
  .ap-ul{text-decoration:underline}
  @media (min-width:640px){.ap-span2{grid-column:span 2 / span 2}}
</style>
@php
    $inputCls = 'w-full border border-outline-variant rounded-lg px-3 py-2 text-sm';
    $labelCls = 'block text-sm font-medium text-primary mb-1.5';
    $social = old('social', $member->social_links ?? []);
    $certRows = array_values(old('certificates', $member->certificates ?? []));
    $certRows[] = [];
    $projRows = array_values(old('projects', $member->projects ?? []));
    $projRows[] = [];
    $selectedIds = array_map('intval', old('agency_portfolio_ids', $member->agency_portfolio_ids ?? []));
    $publicUrl = $member->portfolioUrl();
@endphp

<a href="{{ route('admin.team.index') }}" class="text-xs font-mono text-secondary hover:underline mb-4 inline-block">← Kembali ke Anggota Tim</a>
<h1 class="font-display font-bold text-2xl text-primary mb-1">Portofolio Pribadi</h1>
<p class="text-sm text-on-surface-variant mb-6">{{ $member->name }}@if ($member->position) · {{ $member->position }}@endif</p>

@if ($errors->any())
  <div class="px-4 py-3 rounded-lg text-sm mb-4 max-w-3xl" style="background:#fde8e8;color:#9b1c1c">
    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
  </div>
@endif

<div class="max-w-3xl space-y-5">

  {{-- Status & tautan publik --}}
  <div class="bg-white border border-outline-variant rounded-lg p-6 card-shadow">
    @if ($publicUrl)
      <div class="text-xs font-mono uppercase tracking-wider text-on-surface-variant mb-1">Alamat portofolio</div>
      <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="text-sm text-secondary hover:underline ap-break">{{ $publicUrl }}</a>
      <p class="text-xs text-on-surface-variant mt-2">Status: <span class="font-semibold">{{ $member->portfolio_published ? 'Dipublikasikan' : 'Disembunyikan' }}</span></p>
      <div class="flex flex-wrap gap-2 mt-4">
        <a href="{{ route('admin.team.portfolio.preview', $member) }}" target="_blank" class="px-4 py-2 border border-outline-variant rounded text-sm font-medium">Pratinjau</a>
        <form method="POST" action="{{ route('admin.team.portfolio.publish', $member) }}">
          @csrf
          <button type="submit" class="px-4 py-2 border border-outline-variant rounded text-sm font-medium">{{ $member->portfolio_published ? 'Sembunyikan' : 'Publikasikan' }}</button>
        </form>
      </div>
    @else
      <p class="text-sm text-on-surface-variant mb-4">Portofolio pribadi {{ $member->name }} belum dibuat. Tombol di bawah membuat halaman website otomatis dari data yang sudah ada (posisi, bio, keahlian) dan menarik proyek dari portfolio agency.</p>
      <form method="POST" action="{{ route('admin.team.portfolio.generate', $member) }}">
        @csrf
        <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded text-sm font-medium">Buat Portofolio Otomatis</button>
      </form>
    @endif
    <p class="text-xs text-on-surface-variant mt-4">Nama, foto, email, WhatsApp, dan posisi diedit di <a href="{{ route('admin.team.edit', $member) }}" class="text-secondary ap-ul">data dasar anggota</a>.</p>
  </div>

  <form method="POST" action="{{ route('admin.team.portfolio.update', $member) }}" class="space-y-5">
    @csrf
    @method('PUT')

    {{-- Data diri --}}
    <div class="bg-white border border-outline-variant rounded-lg p-6 card-shadow space-y-4">
      <h2 class="font-semibold text-primary">Data Diri</h2>
      <div>
        <label class="{{ $labelCls }}">Headline</label>
        <input name="headline" value="{{ old('headline', $member->headline) }}" placeholder="mis. Backend Developer yang suka membangun sistem rapi" class="{{ $inputCls }}" />
      </div>
      <div>
        <label class="{{ $labelCls }}">Lokasi</label>
        <input name="location" value="{{ old('location', $member->location) }}" placeholder="mis. Jakarta, Indonesia" class="{{ $inputCls }}" />
      </div>
      <div>
        <label class="{{ $labelCls }}">Tentang Saya</label>
        <textarea name="about" rows="5" class="{{ $inputCls }}">{{ old('about', $member->about) }}</textarea>
      </div>
      <div>
        <label class="{{ $labelCls }}">Keahlian (pisahkan dengan koma)</label>
        <input name="skills" value="{{ old('skills', $member->skills ? implode(', ', $member->skills) : '') }}" placeholder="mis. Laravel, MySQL, UI Design" class="{{ $inputCls }}" />
        <p class="text-xs text-on-surface-variant mt-1">Daftar keahlian yang sama dipakai di halaman data dasar anggota.</p>
      </div>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="show_contact" value="1" @checked(old('show_contact', $member->show_contact)) class="w-4 h-4" />
        <span class="text-sm text-primary">Tampilkan email dan WhatsApp di halaman publik</span>
      </label>
    </div>

    {{-- Media sosial --}}
    <div class="bg-white border border-outline-variant rounded-lg p-6 card-shadow space-y-4">
      <h2 class="font-semibold text-primary">Tautan</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach (['github' => 'GitHub', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'website' => 'Website'] as $key => $label)
          <div>
            <label class="{{ $labelCls }}">{{ $label }}</label>
            <input type="url" name="social[{{ $key }}]" value="{{ $social[$key] ?? '' }}" placeholder="https://" class="{{ $inputCls }}" />
          </div>
        @endforeach
      </div>
    </div>

    {{-- Sertifikat --}}
    <div class="bg-white border border-outline-variant rounded-lg p-6 card-shadow">
      <h2 class="font-semibold text-primary mb-1">Sertifikat</h2>
      <p class="text-xs text-on-surface-variant mb-4">Baris yang namanya kosong diabaikan saat disimpan.</p>
      <div id="cert-list" class="space-y-4">
        @foreach ($certRows as $i => $c)
          <div class="border border-outline-variant rounded-lg p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <input name="certificates[{{ $i }}][name]" value="{{ $c['name'] ?? '' }}" placeholder="Nama sertifikat" class="{{ $inputCls }}" />
            <input name="certificates[{{ $i }}][issuer]" value="{{ $c['issuer'] ?? '' }}" placeholder="Penerbit" class="{{ $inputCls }}" />
            <input name="certificates[{{ $i }}][year]" value="{{ $c['year'] ?? '' }}" placeholder="Tahun" class="{{ $inputCls }}" />
            <input type="url" name="certificates[{{ $i }}][url]" value="{{ $c['url'] ?? '' }}" placeholder="Tautan bukti (opsional)" class="{{ $inputCls }}" />
          </div>
        @endforeach
      </div>
      <button type="button" onclick="addRow('cert-list', 'tpl-cert')" class="mt-4 px-4 py-2 border border-outline-variant rounded text-sm font-medium">+ Tambah sertifikat</button>
    </div>

    {{-- Proyek pribadi --}}
    <div class="bg-white border border-outline-variant rounded-lg p-6 card-shadow">
      <h2 class="font-semibold text-primary mb-1">Proyek Pribadi</h2>
      <p class="text-xs text-on-surface-variant mb-4">Proyek di luar agency. Baris yang judulnya kosong diabaikan.</p>
      <div id="proj-list" class="space-y-4">
        @foreach ($projRows as $i => $pr)
          <div class="border border-outline-variant rounded-lg p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <input name="projects[{{ $i }}][title]" value="{{ $pr['title'] ?? '' }}" placeholder="Judul proyek" class="{{ $inputCls }}" />
            <input name="projects[{{ $i }}][year]" value="{{ $pr['year'] ?? '' }}" placeholder="Tahun" class="{{ $inputCls }}" />
            <input name="projects[{{ $i }}][tech]" value="{{ $pr['tech'] ?? '' }}" placeholder="Teknologi (pisahkan koma)" class="{{ $inputCls }}" />
            <input type="url" name="projects[{{ $i }}][url]" value="{{ $pr['url'] ?? '' }}" placeholder="Tautan proyek (opsional)" class="{{ $inputCls }}" />
            <textarea name="projects[{{ $i }}][description]" rows="2" placeholder="Deskripsi singkat" class="{{ $inputCls }} ap-span2">{{ $pr['description'] ?? '' }}</textarea>
          </div>
        @endforeach
      </div>
      <button type="button" onclick="addRow('proj-list', 'tpl-proj')" class="mt-4 px-4 py-2 border border-outline-variant rounded text-sm font-medium">+ Tambah proyek</button>
    </div>

    {{-- Proyek agency (otomatis dari portfolio agency) --}}
    <div class="bg-white border border-outline-variant rounded-lg p-6 card-shadow">
      <h2 class="font-semibold text-primary mb-1">Proyek Agency</h2>
      <p class="text-xs text-on-surface-variant mb-4">Diambil otomatis dari portfolio agency yang berstatus publish. Kalau agency mengubah datanya, halaman ini ikut berubah.</p>
      @if ($agencyPortfolios->isEmpty())
        <p class="text-sm text-on-surface-variant">Belum ada portfolio agency yang dipublikasikan.</p>
      @else
        <div class="flex gap-2 mb-3">
          <button type="button" onclick="toggleAgency(true)" class="px-3 py-1.5 border border-outline-variant rounded text-xs font-medium">Pilih semua</button>
          <button type="button" onclick="toggleAgency(false)" class="px-3 py-1.5 border border-outline-variant rounded text-xs font-medium">Kosongkan</button>
        </div>
        <div class="space-y-2">
          @foreach ($agencyPortfolios as $ap)
            <label class="flex items-start gap-3 cursor-pointer border border-outline-variant rounded-lg p-3">
              <input type="checkbox" name="agency_portfolio_ids[]" value="{{ $ap->id }}" @checked(in_array($ap->id, $selectedIds, true)) class="agency-check w-4 h-4 mt-0.5" />
              <span class="text-sm text-primary"><span class="font-medium">{{ $ap->name }}</span>
                <span class="text-xs text-on-surface-variant"> · {{ $ap->category }}@if ($ap->project_year) · {{ $ap->project_year }}@endif</span></span>
            </label>
          @endforeach
        </div>
      @endif
    </div>

    <div class="flex justify-end gap-2">
      <a href="{{ route('admin.team.index') }}" class="px-5 py-2.5 border border-outline-variant rounded text-sm font-medium">Batal</a>
      <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded text-sm font-medium">Simpan</button>
    </div>
  </form>
</div>

<template id="tpl-cert">
  <div class="border border-outline-variant rounded-lg p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
    <input name="certificates[__i__][name]" placeholder="Nama sertifikat" class="{{ $inputCls }}" />
    <input name="certificates[__i__][issuer]" placeholder="Penerbit" class="{{ $inputCls }}" />
    <input name="certificates[__i__][year]" placeholder="Tahun" class="{{ $inputCls }}" />
    <input type="url" name="certificates[__i__][url]" placeholder="Tautan bukti (opsional)" class="{{ $inputCls }}" />
  </div>
</template>
<template id="tpl-proj">
  <div class="border border-outline-variant rounded-lg p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
    <input name="projects[__i__][title]" placeholder="Judul proyek" class="{{ $inputCls }}" />
    <input name="projects[__i__][year]" placeholder="Tahun" class="{{ $inputCls }}" />
    <input name="projects[__i__][tech]" placeholder="Teknologi (pisahkan koma)" class="{{ $inputCls }}" />
    <input type="url" name="projects[__i__][url]" placeholder="Tautan proyek (opsional)" class="{{ $inputCls }}" />
    <textarea name="projects[__i__][description]" rows="2" placeholder="Deskripsi singkat" class="{{ $inputCls }} ap-span2"></textarea>
  </div>
</template>

<script>
  function addRow(listId, tplId) {
    var html = document.getElementById(tplId).innerHTML.split('__i__').join(String(Date.now()));
    document.getElementById(listId).insertAdjacentHTML('beforeend', html);
  }
  function toggleAgency(state) {
    document.querySelectorAll('.agency-check').forEach(function (el) { el.checked = state; });
  }
</script>
@endsection
