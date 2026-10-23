@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<h1 class="font-display font-bold text-2xl text-primary mb-6">Dashboard</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
  @foreach ([
    ['Total Client', $totalClients, 'groups'],
    ['Request Baru', $requestsNew, 'inbox'],
    ['Dalam Proses', $requestsInProgress, 'sync'],
    ['Proposal Aktif', $activeProposals, 'description'],
  ] as [$label, $value, $icon])
    <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
      <div class="flex items-center justify-between mb-3">
        <span class="font-mono text-[11px] uppercase tracking-wider text-on-surface-variant">{{ $label }}</span>
        <span class="material-symbols-outlined text-secondary/50">{{ $icon }}</span>
      </div>
      <div class="font-display font-bold text-2xl text-primary">{{ $value }}</div>
    </div>
  @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-primary">Request Terbaru</h2>
      <a href="{{ route('admin.requests.index') }}" class="text-xs font-mono text-secondary hover:underline">Lihat semua →</a>
    </div>
    @forelse ($recentRequests as $r)
      <a href="{{ route('admin.requests.show', $r) }}" class="flex items-center justify-between py-2.5 border-b border-outline-variant last:border-0 hover:bg-surface-container-low -mx-2 px-2 rounded">
        <div class="min-w-0"><div class="text-sm font-medium text-primary truncate">{{ $r->company_name }}</div><div class="text-xs font-mono text-on-surface-variant">{{ $r->number }}</div></div>
        @include('admin.requests._status-badge', ['status' => $r->status])
      </a>
    @empty
      <p class="text-sm text-on-surface-variant">Belum ada request masuk.</p>
    @endforelse
  </div>

  <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-primary">Aktivitas Terbaru</h2>
      <a href="{{ route('admin.activity-log.index') }}" class="text-xs font-mono text-secondary hover:underline whitespace-nowrap">Lihat semua →</a>
    </div>

    @forelse ($recentActivity as $a)
      @php
        $activityDescription = (string) ($a->description ?? '');
        $activityLimit = 70;
        $activityIsLong = mb_strlen($activityDescription) > $activityLimit;
        $activityPreview = $activityIsLong
          ? mb_substr($activityDescription, 0, $activityLimit) . '…'
          : $activityDescription;
      @endphp

      <div class="dashboard-activity-item">
        <span class="material-symbols-outlined shrink-0 text-secondary/50 text-[18px] mt-0.5">bolt</span>
        <div class="dashboard-activity-content">
          <div class="dashboard-activity-description">{{ $activityPreview }}</div>
          <div class="dashboard-activity-meta">
            <span class="text-xs text-on-surface-variant font-mono">{{ $a->created_at->translatedFormat('d M Y, H:i') }}</span>
            @if ($activityIsLong)
              <a href="{{ route('admin.activity-log.index') }}" class="dashboard-detail-link">
                Lihat detail
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
              </a>
            @endif
          </div>
        </div>
      </div>
    @empty
      <p class="text-sm text-on-surface-variant">Belum ada aktivitas.</p>
    @endforelse
  </div>
</div>
@endsection
