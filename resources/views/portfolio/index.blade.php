@extends('layouts.public')

@section('title', 'Portfolio — B!ng0.dev')
@section('description', 'Kumpulan project yang sudah dikerjakan tim B!ng0.dev untuk berbagai klien.')

@section('content')
<section class="max-w-6xl mx-auto px-5 md:px-10 pt-14 pb-4">
  <h1 class="font-display font-bold text-2xl md:text-4xl text-primary mb-3">Portfolio</h1>
  <p class="text-on-surface-variant mb-8">Kumpulan proyek yang sudah kami kerjakan untuk klien kami.</p>
  <div class="flex gap-2 mb-10 overflow-x-auto whitespace-nowrap pb-1 -mx-5 px-5 md:mx-0 md:px-0">
    <a href="{{ route('portfolio.index') }}" class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-mono border {{ !$category ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary' }}">Semua</a>
    @foreach ($categories as $c)
      <a href="{{ route('portfolio.index', ['category' => $c]) }}" class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-mono border {{ $category === $c ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary' }}">{{ $c }}</a>
    @endforeach
  </div>
</section>
<section class="max-w-6xl mx-auto px-5 md:px-10 pb-16">
  @if ($portfolios->count())
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      @foreach ($portfolios as $p)
        @include('portfolio._card', ['p' => $p])
      @endforeach
    </div>
    <div class="mt-10">{{ $portfolios->links() }}</div>
  @else
    <p class="text-on-surface-variant text-sm">Belum ada portfolio pada kategori ini.</p>
  @endif
</section>
@endsection
