@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
<div>
    <div class="activity-log-header">
        <h1 class="text-3xl font-bold text-gray-900">Activity Log</h1>
        <p class="mt-2 text-sm text-gray-500">Riwayat aktivitas administrator pada sistem.</p>
    </div>

    <div class="activity-log-table-card">
        <div class="activity-log-table-scroll">
            <table class="activity-log-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($activities as $a)
                    @php
                        $description = (string) ($a->description ?? '');
                        $descriptionLimit = 90;
                        $activityData = [
                            'time' => $a->created_at?->translatedFormat('d M Y, H:i'),
                            'user' => $a->user?->name ?? 'System',
                            'action' => $a->action,
                            'module' => $a->module,
                            'description' => $description,
                        ];
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap">{{ $a->created_at?->translatedFormat('d M Y, H:i') }}</td>
                        <td>
                            @if ($a->user)
                                <div class="font-medium text-gray-900">{{ $a->user->name }}</div>
                                @if ($a->user->email)<div class="mt-1 text-xs text-gray-500">{{ $a->user->email }}</div>@endif
                            @else
                                <span class="font-medium text-gray-900">System</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap">
                            <span class="inline-flex items-center rounded-md border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700">{{ $a->action }}</span>
                        </td>
                        <td class="whitespace-nowrap">{{ $a->module }}</td>
                        <td>
                            @if (mb_strlen($description) > $descriptionLimit)
                                <div class="activity-log-description">
                                    <span class="activity-log-description-text">{{ mb_substr($description, 0, $descriptionLimit) }}...</span>
                                    <button type="button" class="activity-detail-button" data-activity-detail data-activity="{{ base64_encode(json_encode($activityData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}">Lihat detail</button>
                                </div>
                            @else
                                <span class="activity-log-description-text">{{ $description }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">Belum ada aktivitas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($activities->hasPages())
            <div class="activity-log-pagination">{{ $activities->links() }}</div>
        @endif
    </div>
</div>

<div id="activityDetailModal" class="activity-modal-overlay" aria-hidden="true">
    <div class="activity-modal" role="dialog" aria-modal="true" aria-labelledby="activityDetailTitle">
        <div class="activity-modal-header">
            <div>
                <h2 id="activityDetailTitle" class="text-lg font-bold text-gray-900">Detail Aktivitas</h2>
                <p class="mt-1 text-sm text-gray-500">Informasi lengkap aktivitas administrator.</p>
            </div>
            <button type="button" data-close-activity-modal class="flex h-9 w-9 items-center justify-center rounded-lg text-2xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700" aria-label="Tutup">&times;</button>
        </div>

        <div class="activity-modal-body">
            <div class="activity-modal-field"><div class="activity-modal-label">Waktu</div><div id="detailTime" class="activity-modal-value">-</div></div>
            <div class="activity-modal-field"><div class="activity-modal-label">User</div><div id="detailUser" class="activity-modal-value">-</div></div>
            <div class="activity-modal-field"><div class="activity-modal-label">Aksi</div><div id="detailAction" class="activity-modal-value">-</div></div>
            <div class="activity-modal-field"><div class="activity-modal-label">Modul</div><div id="detailModule" class="activity-modal-value">-</div></div>
            <div class="activity-modal-field"><div class="activity-modal-label">Deskripsi</div><div id="detailDescription" class="activity-modal-description">-</div></div>
        </div>

        <div class="activity-modal-footer">
            <button type="button" data-close-activity-modal class="activity-modal-close">Tutup</button>
        </div>
    </div>
</div>
@endsection
