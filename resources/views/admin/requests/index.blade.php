@extends('layouts.admin')

@section('title', 'Request Jasa')

@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
  <h1 class="font-display font-bold text-2xl text-primary">Request Jasa</h1>
  <form method="GET" action="{{ route('admin.requests.index') }}">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari perusahaan / nomor request..." class="border border-outline-variant rounded-lg px-4 py-2 text-sm w-full sm:w-72" onchange="this.form.submit()" />
  </form>
</div>

<div class="flex gap-2 mb-5 overflow-x-auto whitespace-nowrap pb-1 -mx-5 px-5 md:mx-0 md:px-0">
  <a href="{{ route('admin.requests.index', array_filter(['q' => request('q')])) }}" class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-mono border {{ !request('status') ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary' }}">Semua</a>
  @foreach ($statuses as $s)
    <a href="{{ route('admin.requests.index', array_filter(['status' => $s, 'q' => request('q')])) }}" class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-mono border {{ request('status') === $s ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary' }}">{{ $s }}</a>
  @endforeach
</div>

<div class="bg-white border border-outline-variant rounded-lg overflow-hidden card-shadow overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="bg-surface-container-low text-left font-mono text-[11px] uppercase tracking-wider text-on-surface-variant">
      <th class="px-4 py-3">No. Request</th><th class="px-4 py-3">Perusahaan</th><th class="px-4 py-3 hidden md:table-cell">Jenis</th><th class="px-4 py-3 hidden sm:table-cell">Tanggal</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th>
    </tr></thead>
    <tbody>
    @forelse ($requests as $r)
      <tr class="border-t border-outline-variant hover:bg-surface-container-low">
        <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">{{ $r->number }}</td>
        <td class="px-4 py-3 font-medium">{{ $r->company_name }}</td>
        <td class="px-4 py-3 hidden md:table-cell text-on-surface-variant">{{ $r->website_type }}</td>
        <td class="px-4 py-3 hidden sm:table-cell text-on-surface-variant text-xs whitespace-nowrap">{{ $r->created_at->translatedFormat('d M Y') }}</td>
        <td class="px-4 py-3">@include('admin.requests._status-badge', ['status' => $r->status])</td>
        <td class="px-4 py-3"><a href="{{ route('admin.requests.show', $r) }}" class="text-secondary text-xs font-mono hover:underline whitespace-nowrap">Detail →</a></td>
      </tr>
    @empty
      <tr><td colspan="6" class="px-4 py-10 text-center text-on-surface-variant text-sm">Tidak ada request ditemukan.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="mt-6">{{ $requests->links() }}</div>
@endsection
