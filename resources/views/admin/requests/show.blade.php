@extends('layouts.admin')

@section('title', $request->number)

@section('content')
<a href="{{ route('admin.requests.index') }}" class="text-xs font-mono text-secondary hover:underline mb-4 inline-block">← Kembali ke daftar</a>
<div class="flex items-center justify-between flex-wrap gap-3 mb-6">
  <div><h1 class="font-display font-bold text-2xl text-primary">{{ $request->company_name }}</h1><p class="font-mono text-xs text-on-surface-variant">{{ $request->number }}</p></div>
  @include('admin.requests._status-badge', ['status' => $request->status])
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">
    <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
      <h2 class="font-semibold text-primary mb-4">Detail Permintaan</h2>
      <dl class="grid grid-cols-2 gap-y-3 text-sm">
        <dt class="text-on-surface-variant">Contact Person</dt><dd>{{ $request->contact_name }}</dd>
        <dt class="text-on-surface-variant">HP/WhatsApp</dt><dd>{{ $request->phone }}</dd>
        <dt class="text-on-surface-variant">Email</dt><dd>{{ $request->email }}</dd>
        <dt class="text-on-surface-variant">Nama Proyek</dt><dd>{{ $request->project_name }}</dd>
        <dt class="text-on-surface-variant">Jenis Website</dt><dd>{{ $request->website_type }}</dd>
      </dl>
      <div class="mt-4"><div class="text-on-surface-variant text-sm mb-1">Deskripsi</div><div class="text-sm">{{ $request->description }}</div></div>
      @if ($request->functional_requirements)
        <div class="mt-4"><div class="text-on-surface-variant text-sm mb-1">Fitur Diminta</div>
          <div class="flex flex-wrap gap-2 mt-1">
            @foreach ($request->functional_requirements as $f)<span class="px-2 py-1 bg-surface-container-low rounded text-xs">{{ $f }}</span>@endforeach
          </div>
        </div>
      @endif
      @if ($request->other_requirements)
        <div class="mt-4"><div class="text-on-surface-variant text-sm mb-1">Kebutuhan Lain</div><div class="text-sm">{{ $request->other_requirements }}</div></div>
      @endif
    </div>

    <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
      <h2 class="font-semibold text-primary mb-3">Catatan Internal</h2>
      <div class="space-y-2 mb-4">
        @forelse ($request->notes as $n)
          <div class="text-sm bg-surface-container-low rounded p-3">
            <span class="text-xs font-mono text-on-surface-variant block mb-1">{{ $n->created_at->translatedFormat('d M Y, H:i') }} @if($n->user) · {{ $n->user->name }} @endif</span>
            {{ $n->note }}
          </div>
        @empty
          <p class="text-sm text-on-surface-variant">Belum ada catatan.</p>
        @endforelse
      </div>
      <form method="POST" action="{{ route('admin.requests.notes.store', $request) }}" class="flex items-center gap-2">
        @csrf
        <input name="note" placeholder="Tambah catatan..." class="flex-1 border border-outline-variant rounded-lg px-3 py-2 text-sm" required />
        <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded text-sm font-medium">Tambah</button>
      </form>
    </div>
  </div>

  <div class="space-y-6">
    <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
      <h2 class="font-semibold text-primary mb-3">Ubah Status</h2>
      <form method="POST" action="{{ route('admin.requests.status', $request) }}">
        @csrf
        @method('PATCH')
        <select name="status" onchange="this.form.submit()" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm mb-2">
          @foreach ($statuses as $s)
            <option value="{{ $s }}" @selected($request->status === $s)>{{ $s }}</option>
          @endforeach
        </select>
      </form>
      <p class="text-xs text-on-surface-variant">Perubahan status otomatis tercatat di activity log.</p>
    </div>

    <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
      <h2 class="font-semibold text-primary mb-1">Pembagian Tugas</h2>
      <p class="text-xs text-on-surface-variant mb-4">Pilih anggota berdasarkan kecocokan keahlian dengan request. Setelah memilih, isi tugas dan deadline masing-masing.</p>

      @if ($teamMembers->count())
        <form method="POST" action="{{ route('admin.requests.assign-team', $request) }}">
          @csrf
          <div class="space-y-3 mb-4 max-h-[520px] overflow-y-auto pr-1">
            @foreach ($teamMembers as $m)
              @php $assigned = $request->teamMembers->firstWhere('id', $m->id); @endphp
              <div class="border border-outline-variant rounded-lg p-3 {{ $assigned ? 'bg-surface-container-low/50' : 'bg-white' }}">
                <label class="flex items-start gap-2 cursor-pointer">
                  <input type="checkbox" name="team_member_ids[]" value="{{ $m->id }}" class="mt-0.5 w-4 h-4" @checked($assigned)>
                  <span class="text-sm min-w-0">
                    <span class="font-medium text-primary block">{{ $m->name }}</span>
                    <span class="text-on-surface-variant text-xs block">{{ $m->position ?: 'Anggota Tim' }}</span>
                    @if ($m->skills)
                      <span class="text-[10px] text-on-surface-variant block mt-1">{{ implode(', ', $m->skills) }}</span>
                    @endif
                  </span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3 pl-6">
                  <div class="sm:col-span-2">
                    <label class="block text-[11px] font-mono text-on-surface-variant mb-1">TUGAS</label>
                    <textarea name="tasks[{{ $m->id }}]" rows="3" placeholder="Contoh: Buat wireframe halaman utama dan dashboard..." class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs">{{ old('tasks.'.$m->id, $assigned?->pivot?->task) }}</textarea>
                  </div>
                  <div>
                    <label class="block text-[11px] font-mono text-on-surface-variant mb-1">DEADLINE</label>
                    <input type="date" name="deadlines[{{ $m->id }}]" value="{{ old('deadlines.'.$m->id, $assigned?->pivot?->deadline) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs" />
                  </div>
                  <div>
                    <label class="block text-[11px] font-mono text-on-surface-variant mb-1">STATUS</label>
                    <select name="task_statuses[{{ $m->id }}]" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs">
                      <option value="assigned" @selected(($assigned?->pivot?->task_status ?? 'assigned') === 'assigned')>Ditugaskan</option>
                      <option value="in_progress" @selected(($assigned?->pivot?->task_status ?? '') === 'in_progress')>Sedang Dikerjakan</option>
                      <option value="completed" @selected(($assigned?->pivot?->task_status ?? '') === 'completed')>Selesai</option>
                    </select>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
          <button type="submit" class="w-full px-4 py-2 bg-primary text-on-primary rounded text-sm font-medium">Simpan Pembagian Tugas</button>
        </form>
      @else
        <p class="text-sm text-on-surface-variant mb-2">Belum ada anggota tim aktif.</p>
        <a href="{{ route('admin.team.create') }}" class="text-xs font-mono text-secondary hover:underline">+ Tambah anggota tim →</a>
      @endif
    </div>

    @if ($assignedTeamMembers->count())
      <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
        <h2 class="font-semibold text-primary mb-1">Kirim Pembagian Tugas</h2>
        <p class="text-xs text-on-surface-variant mb-4">Template otomatis mengambil nama anggota, request, tugas, dan deadline. Kamu bisa mengubah isi sebelum mengirim.</p>

        <div class="space-y-4">
          @foreach ($assignedTeamMembers as $m)
            @php
              $messages = $assignmentMessages[$m->id] ?? ['whatsapp' => '', 'email' => ''];
              $email = $m->email ?: optional($m->user)->email;
            @endphp
            <details class="border border-outline-variant rounded-lg overflow-hidden" @if($loop->first) open @endif>
              <summary class="cursor-pointer px-3 py-3 bg-surface-container-low flex items-center justify-between gap-2">
                <span class="text-sm font-medium text-primary">{{ $m->name }}</span>
                <span class="text-[10px] font-mono text-on-surface-variant">{{ $m->position ?: 'Anggota Tim' }}</span>
              </summary>
              <div class="p-3 space-y-3">
                <div class="text-xs text-on-surface-variant grid grid-cols-1 gap-1">
                  <span>WhatsApp: <strong class="text-primary">{{ $m->phone ?: 'belum diisi' }}</strong></span>
                  <span>Email: <strong class="text-primary">{{ $email ?: 'belum diisi' }}</strong></span>
                  <span>Tugas: <strong class="text-primary">{{ $m->pivot->task ?: 'Belum diisi' }}</strong></span>
                  <span>Deadline: <strong class="text-primary">{{ $m->pivot->deadline ? \Carbon\Carbon::parse($m->pivot->deadline)->translatedFormat('d M Y') : 'Belum ditentukan' }}</strong></span>
                </div>

                <div>
                  <label class="block text-[11px] font-mono text-on-surface-variant mb-1">TEMPLATE WHATSAPP</label>
                  <form method="POST" action="{{ route('admin.requests.assignment-whatsapp', [$request, $m]) }}">
                    @csrf
                    <textarea name="content" rows="8" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs leading-relaxed">{{ $messages['whatsapp'] }}</textarea>
                    <button type="submit" class="mt-2 w-full px-3 py-2 border border-primary text-primary rounded-lg text-xs font-medium hover:bg-surface-container-low">Buka WhatsApp dengan Template</button>
                  </form>
                </div>

                <div class="border-t border-outline-variant pt-3">
                  <label class="block text-[11px] font-mono text-on-surface-variant mb-1">TEMPLATE EMAIL</label>
                  <form method="POST" action="{{ route('admin.requests.assignment-email', [$request, $m]) }}">
                    @csrf
                    <textarea name="content" rows="9" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs leading-relaxed">{{ $messages['email'] }}</textarea>
                    <button type="submit" class="mt-2 w-full px-3 py-2 bg-primary text-on-primary rounded-lg text-xs font-medium">Kirim Email</button>
                  </form>
                </div>
              </div>
            </details>
          @endforeach
        </div>
      </div>
    @endif

    <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
      <h2 class="font-semibold text-primary mb-3">Client</h2>
      @if ($request->client)
        <p class="text-sm font-medium">{{ $request->client->company_name }}</p>
        <p class="text-xs text-on-surface-variant mb-3">{{ $request->client->contact_person }} · {{ $request->client->phone }}</p>
        <a href="{{ route('admin.clients.index') }}" class="text-xs font-mono text-secondary hover:underline">Lihat data client →</a>
      @else
        <p class="text-sm text-on-surface-variant mb-3">Request ini belum terhubung ke record client.</p>
        <form method="POST" action="{{ route('admin.requests.convert', $request) }}">
          @csrf
          <button type="submit" class="w-full px-4 py-2 border border-primary text-primary rounded text-sm font-medium hover:bg-surface-container-low">+ Buat / Hubungkan Client</button>
        </form>
      @endif
    </div>
  </div>
</div>
@endsection
