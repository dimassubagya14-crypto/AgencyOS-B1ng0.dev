@extends('layouts.admin')

@section('title', 'Anggota Tim')

@section('content')
<div class="flex items-center justify-between mb-6">
  <h1 class="font-display font-bold text-2xl text-primary">Anggota Tim</h1>
  <a href="{{ route('admin.team.create') }}" class="px-4 py-2 bg-primary text-on-primary rounded text-sm font-medium">+ Tambah Anggota</a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
  @forelse ($members as $m)
    <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-12 h-12 rounded-full bg-surface-container-high overflow-hidden flex items-center justify-center flex-shrink-0">
          @if ($m->photo)
            <img src="{{ route('media', ['path' => $m->photo]) }}" alt="Foto {{ $m->name }}" class="w-full h-full object-cover" />
          @else
            <span class="material-symbols-outlined text-secondary/40">person</span>
          @endif
        </div>
        <div class="min-w-0">
          <div class="font-semibold text-primary truncate">{{ $m->name }}</div>
          <div class="text-xs text-on-surface-variant truncate">{{ $m->position }}</div>
        </div>
        @unless ($m->is_active)
          <span class="ml-auto px-2 py-0.5 rounded-full text-[10px] font-mono bg-outline-variant text-on-surface-variant flex-shrink-0">nonaktif</span>
        @endunless
      </div>
      @if ($m->email || $m->phone || optional($m->user)->email)
        <div class="space-y-1 mb-3 text-[11px] text-on-surface-variant">
          @if ($m->email || optional($m->user)->email)<div>✉ {{ $m->email ?: optional($m->user)->email }}</div>@endif
          @if ($m->phone)<div>☏ {{ $m->phone }}</div>@endif
        </div>
      @endif
      @if ($m->skills && count($m->skills))
        <div class="flex flex-wrap gap-1.5 mb-3">
          @foreach ($m->skills as $s)<span class="px-2 py-0.5 bg-surface-container-low rounded text-[11px] font-mono">{{ $s }}</span>@endforeach
        </div>
      @endif
      <div class="team-actions">
        <a href="{{ route('admin.team.edit', $m) }}">Edit</a>
        <form method="POST" action="{{ route('admin.team.destroy', $m) }}" onsubmit="return confirm('Hapus {{ $m->name }} dari tim?');">
          @csrf @method('DELETE')
          <button type="submit">Hapus</button>
        </form>
      </div>
    </div>
  @empty
    <p class="text-sm text-on-surface-variant col-span-3">Belum ada anggota tim. Klik "+ Tambah Anggota" untuk mulai — data ini juga dipakai untuk menentukan siapa yang garap tiap request.</p>
  @endforelse
</div>
@endsection
