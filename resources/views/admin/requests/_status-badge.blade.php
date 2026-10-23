@php
  $statusColors = [
    'Baru' => 'bg-secondary-container text-white',
    'Diperiksa' => 'bg-surface-container-highest text-on-surface',
    'Konsultasi' => 'bg-surface-container-highest text-on-surface',
    'Proposal Dibuat' => 'bg-secondary-bright/15 text-secondary',
    'Proposal Dikirim' => 'bg-secondary-bright/15 text-secondary',
    'Negosiasi' => 'bg-secondary-bright/15 text-secondary',
    'Disetujui' => 'bg-success-container text-success',
    'Ditolak' => 'bg-error-container text-on-error-container',
    'Batal' => 'bg-outline-variant text-on-surface-variant',
  ];
@endphp
<span class="px-2.5 py-1 rounded-full text-xs font-medium font-mono {{ $statusColors[$status] ?? 'bg-outline-variant' }}">{{ $status }}</span>
