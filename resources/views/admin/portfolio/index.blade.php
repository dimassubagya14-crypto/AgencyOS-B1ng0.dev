@extends('layouts.admin')

@section('title', 'Portfolio')

@section('content')
<div class="flex items-center justify-between mb-6">
  <h1 class="font-display font-bold text-2xl text-primary">Portfolio</h1>
  <a href="{{ route('admin.portfolio.create') }}" class="px-4 py-2 bg-primary text-on-primary rounded text-sm font-medium">+ Tambah Portfolio</a>
</div>

  <div class="bg-white border border-outline-variant rounded-xl p-4 mb-5 card-shadow">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
      <div>
        <h2 class="font-semibold text-primary">Export Gabungan Portfolio</h2>
        <p class="text-xs text-on-surface-variant mt-1">Centang beberapa portfolio untuk membuat satu file PDF atau PowerPoint yang berisi seluruh project terpilih.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md border border-outline-variant bg-surface-container-low px-3 py-2 text-xs font-semibold text-primary hover:bg-surface-container-high">
          <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> PDF Terpilih
        </button>
        <button type="button" id="export-ppt-selected" class="inline-flex items-center gap-1.5 rounded-md border border-outline-variant bg-surface-container-low px-3 py-2 text-xs font-semibold text-primary hover:bg-surface-container-high">
          <span class="material-symbols-outlined text-[16px]">slideshow</span> PowerPoint Terpilih
        </button>
        <label class="inline-flex items-center gap-2 text-xs font-medium text-on-surface-variant cursor-pointer ml-1">
          <input type="checkbox" id="select-all-portfolios" class="rounded">
          Pilih semua
        </label>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @forelse ($portfolios as $p)
      <div class="bg-white border border-outline-variant rounded-xl p-5 card-shadow">
        <div class="flex justify-between items-start mb-2 gap-3">
          <label class="inline-flex items-center gap-2 text-xs text-on-surface-variant cursor-pointer">
            <input type="checkbox" name="portfolio_ids[]" value="{{ $p->id }}" class="portfolio-checkbox rounded">
            Pilih
          </label>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $p->status === 'publish' ? 'bg-success-container text-success' : 'bg-outline-variant text-on-surface-variant' }}">{{ $p->status }}</span>
        </div>
        <h3 class="font-semibold text-primary mb-1">{{ $p->name }}</h3>
        <p class="text-xs text-on-surface-variant mb-3 line-clamp-2">{{ $p->description }}</p>
        @if ($p->status === 'publish')
          <a href="{{ route('portfolio.show', $p->slug) }}" target="_blank" class="text-xs font-mono text-secondary hover:underline block mb-3">Lihat microsite → /portfolio/{{ $p->slug }}</a>
        @endif
        <div class="flex items-center gap-3 flex-wrap">
          <a href="{{ route('admin.portfolio.edit', $p) }}" class="text-xs font-mono text-secondary hover:underline">Edit</a>
          <a href="{{ route('admin.portfolio.pdf', $p) }}" class="inline-flex items-center gap-1 rounded-md border border-outline-variant bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-primary hover:bg-surface-container-high" title="Generate PDF portfolio">
            <span class="material-symbols-outlined text-[15px]">picture_as_pdf</span> PDF
          </a>
          <a href="{{ route('admin.portfolio.powerpoint', $p) }}" class="inline-flex items-center gap-1 rounded-md border border-outline-variant bg-surface-container-low px-3 py-1.5 text-xs font-semibold text-primary hover:bg-surface-container-high" title="Generate PowerPoint portfolio">
            <span class="material-symbols-outlined text-[15px]">slideshow</span> PowerPoint
          </a>
          <form method="POST" action="{{ route('admin.portfolio.publish', $p) }}" class="inline">
            @csrf @method('PATCH')
            <button type="submit" class="text-xs font-mono text-secondary hover:underline">{{ $p->status === 'publish' ? 'Unpublish' : 'Publish' }}</button>
          </form>
          <form method="POST" action="{{ route('admin.portfolio.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus portfolio \'{{ $p->name }}\'?');">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs font-mono text-error hover:underline">Hapus</button>
          </form>
        </div>
      </div>
    @empty
      <p class="text-sm text-on-surface-variant col-span-2">Belum ada portfolio. Klik "+ Tambah Portfolio" untuk mulai.</p>
    @endforelse
  </div>
<form id="portfolio-pdf-form" method="POST" action="{{ route('admin.portfolio.export.pdf') }}" class="hidden">
  @csrf
</form>
<form id="portfolio-ppt-form" method="POST" action="{{ route('admin.portfolio.export.powerpoint') }}" class="hidden">
  @csrf
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const all = document.getElementById('select-all-portfolios');
  const boxes = () => Array.from(document.querySelectorAll('.portfolio-checkbox'));
  const pptForm = document.getElementById('portfolio-ppt-form');
  const pdfForm = document.getElementById('portfolio-pdf-form');
  const pptButton = document.getElementById('export-ppt-selected');

  function selected() { return boxes().filter(b => b.checked); }
  function ensureSelection() {
    if (!selected().length) {
      alert('Pilih minimal satu portfolio terlebih dahulu.');
      return false;
    }
    return true;
  }

  all?.addEventListener('change', function () { boxes().forEach(b => b.checked = all.checked); });
  boxes().forEach(b => b.addEventListener('change', function () {
    all.checked = boxes().length > 0 && boxes().every(x => x.checked);
  }));
  pdfForm?.addEventListener('submit', function (e) {
    if (!ensureSelection()) { e.preventDefault(); return; }
  });

  const pdfExportButton = document.querySelector('button[type="submit"]');
  pdfExportButton?.addEventListener('click', function (e) {
    if (!ensureSelection()) { e.preventDefault(); return; }
    pdfForm.querySelectorAll('input[name="portfolio_ids[]"]').forEach(x => x.remove());
    selected().forEach(box => {
      const input = document.createElement('input');
      input.type = 'hidden'; input.name = 'portfolio_ids[]'; input.value = box.value;
      pdfForm.appendChild(input);
    });
    e.preventDefault();
    pdfForm.submit();
  });
  pptButton?.addEventListener('click', function () {
    if (!ensureSelection()) return;
    pptForm.querySelectorAll('input[name="portfolio_ids[]"]').forEach(x => x.remove());
    selected().forEach(box => {
      const input = document.createElement('input');
      input.type = 'hidden'; input.name = 'portfolio_ids[]'; input.value = box.value;
      pptForm.appendChild(input);
    });
    pptForm.submit();
  });
});
</script>
<div class="mt-6">{{ $portfolios->links() }}</div>
@endsection
