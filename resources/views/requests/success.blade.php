@extends('layouts.public')

@section('title', 'Request Terkirim — B!ng0.dev')
@section('description', 'Request jasa Anda berhasil terkirim ke B!ng0.dev.')

@section('content')
<section class="max-w-lg mx-auto px-5 md:px-10 py-24 text-center">
  <div class="w-16 h-16 rounded-full bg-success-container flex items-center justify-center mx-auto mb-6">
    <span class="material-symbols-outlined text-success text-3xl">check</span>
  </div>
  <h1 class="font-display font-bold text-2xl text-primary mb-2">Request Terkirim!</h1>
  <p class="text-on-surface-variant mb-1">Nomor request Anda:</p>
  <p class="font-mono text-lg font-semibold text-secondary mb-6">{{ $number }}</p>
  <p class="text-sm text-on-surface-variant mb-8">Tim kami akan menghubungi Anda melalui email/WhatsApp yang terdaftar.</p>
  <a href="{{ route('home') }}" class="inline-block bg-primary text-on-primary px-6 py-3 rounded font-semibold">Kembali ke Beranda</a>
</section>
@endsection
