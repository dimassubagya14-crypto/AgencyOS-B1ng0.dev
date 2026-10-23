@extends('layouts.admin')

@section('title', $member->exists ? 'Edit Anggota Tim' : 'Tambah Anggota Tim')

@section('content')
<a href="{{ route('admin.team.index') }}" class="text-xs font-mono text-secondary hover:underline mb-4 inline-block">← Kembali ke Anggota Tim</a>
<h1 class="font-display font-bold text-2xl text-primary mb-6">{{ $member->exists ? 'Edit Anggota Tim' : 'Tambah Anggota Tim' }}</h1>

<form method="POST" action="{{ $member->exists ? route('admin.team.update', $member) : route('admin.team.store') }}" enctype="multipart/form-data" class="bg-white border border-outline-variant rounded-lg p-6 card-shadow max-w-xl space-y-4">
  @csrf
  @if ($member->exists) @method('PATCH') @endif

  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Nama</label>
    <input name="name" value="{{ old('name', $member->name) }}" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Email Anggota</label>
      <input type="email" name="email" value="{{ old('email', $member->email) }}" placeholder="nama@email.com" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">WhatsApp</label>
      <input type="text" name="phone" value="{{ old('phone', $member->phone) }}" placeholder="08xxxxxxxxxx" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Posisi / Jabatan</label>
    <input name="position" value="{{ old('position', $member->position) }}" placeholder="mis. Backend Developer, UI/UX Designer" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Keahlian (pisahkan dengan koma)</label>
    <input name="skills" value="{{ old('skills', $member->skills ? implode(', ', $member->skills) : '') }}" placeholder="mis. Laravel, E-Commerce, UI Design" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    <p class="text-xs text-on-surface-variant mt-1">Dipakai sistem untuk menyarankan siapa yang cocok garap request tertentu (dicocokkan dengan jenis website).</p>
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Bio Singkat</label>
    <textarea name="bio" rows="3" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('bio', $member->bio) }}</textarea>
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Foto</label>
    @if ($member->photo)<img src="{{ route('media', ['path' => $member->photo]) }}" class="w-16 h-16 rounded-full object-cover border border-outline-variant mb-2" alt="Foto saat ini" />@endif
    <input type="file" name="photo" accept="image/*" class="w-full text-sm" />
  </div>
  <label class="flex items-center gap-2 cursor-pointer">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $member->exists ? $member->is_active : true)) class="w-4 h-4" />
    <span class="text-sm text-primary">Aktif (tampil sebagai opsi assignment)</span>
  </label>

  <div class="flex justify-end gap-2 pt-2">
    <a href="{{ route('admin.team.index') }}" class="px-5 py-2.5 border border-outline-variant rounded text-sm font-medium">Batal</a>
    <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded text-sm font-medium">Simpan</button>
  </div>
</form>
@endsection
