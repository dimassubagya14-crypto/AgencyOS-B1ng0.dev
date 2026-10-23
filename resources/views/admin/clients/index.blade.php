@extends('layouts.admin')

@section('title', 'Client')

@section('content')
<div class="flex items-center justify-between mb-6"><h1 class="font-display font-bold text-2xl text-primary">Client</h1></div>

<div class="bg-white border border-outline-variant rounded-lg overflow-hidden card-shadow overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="bg-surface-container-low text-left font-mono text-[11px] uppercase tracking-wider text-on-surface-variant">
      <th class="px-4 py-3">Nama Perusahaan</th><th class="px-4 py-3 hidden sm:table-cell">Contact Person</th><th class="px-4 py-3 hidden md:table-cell">Kontak</th><th class="px-4 py-3">Jumlah Request</th><th class="px-4 py-3">Catatan</th>
    </tr></thead>
    <tbody>
    @forelse ($clients as $c)
      <tr class="border-t border-outline-variant hover:bg-surface-container-low">
        <td class="px-4 py-3 font-medium">{{ $c->company_name }}</td>
        <td class="px-4 py-3 hidden sm:table-cell">{{ $c->contact_person }}</td>
        <td class="px-4 py-3 hidden md:table-cell text-on-surface-variant text-xs">{{ $c->phone }} · {{ $c->email }}</td>
        <td class="px-4 py-3">{{ $c->service_requests_count }}</td>
        <td class="px-4 py-3">
          <form method="POST" action="{{ route('admin.clients.update', $c) }}" class="flex gap-1">
            @csrf @method('PATCH')
            <input type="hidden" name="company_name" value="{{ $c->company_name }}">
            <input type="hidden" name="contact_person" value="{{ $c->contact_person }}">
            <input type="hidden" name="phone" value="{{ $c->phone }}">
            <input type="hidden" name="email" value="{{ $c->email }}">
            <input name="internal_notes" value="{{ $c->internal_notes }}" placeholder="Tambah catatan..." onchange="this.form.submit()" class="border border-outline-variant rounded px-2 py-1 text-xs w-40" />
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="5" class="px-4 py-10 text-center text-on-surface-variant text-sm">Belum ada client. Client otomatis terbuat saat request dihubungkan.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="mt-6">{{ $clients->links() }}</div>
@endsection
