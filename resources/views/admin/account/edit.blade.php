@extends('layouts.admin')

@section('title', 'Akun Admin')

@section('content')
<div class="max-w-2xl">
  <a href="{{ route('admin.dashboard') }}" class="text-xs font-mono text-secondary hover:underline mb-4 inline-block">← Kembali ke Dashboard</a>
  <h1 class="font-display font-bold text-2xl text-primary mb-1">Akun Admin</h1>
  <p class="text-sm text-on-surface-variant mb-6">Ganti email dan password default akun admin dari halaman ini. Password saat ini wajib dimasukkan sebagai konfirmasi keamanan.</p>

  <div class="bg-white border border-outline-variant rounded-xl p-5 md:p-6 card-shadow">
    <form method="POST" action="{{ route('admin.account.update') }}" class="space-y-5">
      @csrf
      @method('PATCH')

      <div>
        <label class="block text-sm font-medium text-primary mb-1.5">Nama Admin</label>
        <input name="name" value="{{ old('name', $user->name) }}" required class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm" />
      </div>

      <div>
        <label class="block text-sm font-medium text-primary mb-1.5">Email Login</label>
        <input name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm" />
        <p class="text-xs text-on-surface-variant mt-1.5">Email ini juga menjadi tujuan OTP saat login.</p>
      </div>

      <div class="border-t border-outline-variant pt-5">
        <label class="block text-sm font-medium text-primary mb-1.5">Password Saat Ini</label>
        <input name="current_password" type="password" required autocomplete="current-password" class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-primary mb-1.5">Password Baru</label>
          <input name="password" type="password" minlength="8" autocomplete="new-password" class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm" />
          <p class="text-xs text-on-surface-variant mt-1.5">Kosongkan jika hanya ingin mengganti email/nama.</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-primary mb-1.5">Konfirmasi Password Baru</label>
          <input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm" />
        </div>
      </div>

      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-xs text-amber-800">
        Setelah diganti, password default <strong>tidak perlu lagi digunakan</strong>. Simpan kredensial baru Anda dengan aman.
      </div>

      <div class="flex justify-end">
        <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-semibold">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
@endsection
