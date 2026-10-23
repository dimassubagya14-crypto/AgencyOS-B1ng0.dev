@extends('layouts.admin')

@section('title', $portfolio->exists ? 'Edit Portfolio' : 'Tambah Portfolio')

@section('content')
<a href="{{ route('admin.portfolio.index') }}" class="text-xs font-mono text-secondary hover:underline mb-4 inline-block">← Kembali ke Portfolio</a>
<h1 class="font-display font-bold text-2xl text-primary mb-1">{{ $portfolio->exists ? 'Edit Portfolio' : 'Tambah Portfolio' }}</h1>
<p class="text-sm text-on-surface-variant mb-6">
  Konten di sini yang tampil di halaman microsite publik
  @if ($portfolio->exists)<span class="font-mono">/portfolio/{{ $portfolio->slug }}</span>@else (URL dibuat otomatis dari nama project) @endif.
</p>

<form method="POST" action="{{ $portfolio->exists ? route('admin.portfolio.update', $portfolio) : route('admin.portfolio.store') }}" enctype="multipart/form-data" class="space-y-6 max-w-2xl">
  @csrf
  @if ($portfolio->exists) @method('PATCH') @endif

  <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow space-y-4">
    <h2 class="font-semibold text-primary">Info Dasar</h2>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Nama Project</label>
      <input name="name" value="{{ old('name', $portfolio->name) }}" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Tagline (satu baris untuk hero microsite)</label>
      <input name="tagline" value="{{ old('tagline', $portfolio->tagline) }}" placeholder="mis. Sistem POS multi-cabang dengan sinkronisasi real-time" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-primary mb-1.5">Nama Client</label>
        <input name="client_name" value="{{ old('client_name', $portfolio->client_name) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-primary mb-1.5">Kategori</label>
        <select name="category" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">
          @foreach ($websiteTypes as $t)
            <option value="{{ $t }}" @selected(old('category', $portfolio->category) === $t)>{{ $t }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-primary mb-1.5">Durasi Pengerjaan</label>
        <input name="duration" value="{{ old('duration', $portfolio->duration) }}" placeholder="mis. 6 minggu" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-primary mb-1.5">Tahun</label>
        <input name="project_year" value="{{ old('project_year', $portfolio->project_year) }}" placeholder="2026" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Deskripsi</label>
      <textarea name="description" rows="3" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('description', $portfolio->description) }}</textarea>
    </div>
  </div>

  <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow space-y-4">
    <h2 class="font-semibold text-primary">Cerita Project</h2>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Masalah</label>
      <textarea name="problem" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('problem', $portfolio->problem) }}</textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Solusi</label>
      <textarea name="solution" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('solution', $portfolio->solution) }}</textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Hasil / Dampak (opsional, ditampilkan menonjol di microsite)</label>
      <textarea name="results" rows="2" placeholder="mis. Waktu transaksi turun 40%, stok antar cabang selalu akurat" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('results', $portfolio->results) }}</textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Fitur (pisahkan dengan koma)</label>
      <input name="features" value="{{ old('features', $portfolio->features ? implode(', ', $portfolio->features) : '') }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Teknologi (pisahkan dengan koma)</label>
      <input name="technologies" value="{{ old('technologies', $portfolio->technologies ? implode(', ', $portfolio->technologies) : '') }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">URL Project (opsional)</label>
      <input name="url" type="url" value="{{ old('url', $portfolio->url) }}" placeholder="https://" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
  </div>

  <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow space-y-4">
    <h2 class="font-semibold text-primary">Testimonial (opsional)</h2>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Kutipan</label>
      <textarea name="testimonial_text" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('testimonial_text', $portfolio->testimonial_text) }}</textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Nama & Jabatan Pemberi Testimoni</label>
      <input name="testimonial_author" value="{{ old('testimonial_author', $portfolio->testimonial_author) }}" placeholder="mis. Budi, Owner Sigma Retail" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
  </div>

  <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow space-y-5">
    <div>
      <h2 class="font-semibold text-primary">Gambar Portfolio</h2>
      <p class="text-xs text-on-surface-variant mt-1">Semua gambar disimpan di storage publik dan ditampilkan melalui URL aplikasi yang aman untuk localhost, LAN, maupun VS Code Dev Tunnel.</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Thumbnail</label>
      @if ($portfolio->thumbnail)
        <div class="flex items-center gap-3 mb-2">
          <img src="{{ route('media', ['path' => $portfolio->thumbnail]) }}" class="w-32 h-20 object-cover rounded border border-outline-variant" alt="Thumbnail saat ini" />
          <form method="POST" action="{{ route('admin.portfolio.image.delete', $portfolio) }}" onsubmit="return confirm('Hapus thumbnail ini?');">
            @csrf @method('DELETE')
            <input type="hidden" name="type" value="thumbnail" />
            <button type="submit" class="px-3 py-2 rounded-lg border border-red-200 text-red-600 text-xs font-semibold hover:bg-red-50">Hapus gambar</button>
          </form>
        </div>
      @endif
      <input type="file" name="thumbnail" accept="image/jpeg,image/png" class="w-full text-sm" />
      <p class="text-xs text-on-surface-variant mt-1">Format JPG/PNG, maksimal 4 MB. Format ini juga dipastikan kompatibel dengan PDF & PowerPoint.</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Banner</label>
      @if ($portfolio->banner)
        <div class="flex items-center gap-3 mb-2">
          <img src="{{ route('media', ['path' => $portfolio->banner]) }}" class="w-48 h-24 object-cover rounded border border-outline-variant" alt="Banner saat ini" />
          <form method="POST" action="{{ route('admin.portfolio.image.delete', $portfolio) }}" onsubmit="return confirm('Hapus banner ini?');">
            @csrf @method('DELETE')
            <input type="hidden" name="type" value="banner" />
            <button type="submit" class="px-3 py-2 rounded-lg border border-red-200 text-red-600 text-xs font-semibold hover:bg-red-50">Hapus gambar</button>
          </form>
        </div>
      @endif
      <input type="file" name="banner" accept="image/jpeg,image/png" class="w-full text-sm" />
      <p class="text-xs text-on-surface-variant mt-1">Untuk hero microsite dan cover PowerPoint. Disarankan rasio 16:9.</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Galeri</label>
      @if ($portfolio->gallery && count($portfolio->gallery))
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-3">
          @foreach ($portfolio->gallery as $index => $img)
            <div class="border border-outline-variant rounded-lg p-2 bg-surface-container-low">
              <img src="{{ route('media', ['path' => $img]) }}" class="w-full h-28 object-cover rounded" alt="Galeri {{ $index + 1 }}" />
              <form method="POST" action="{{ route('admin.portfolio.image.delete', $portfolio) }}" class="mt-2" onsubmit="return confirm('Hapus gambar galeri ini?');">
                @csrf @method('DELETE')
                <input type="hidden" name="type" value="gallery" />
                <input type="hidden" name="index" value="{{ $index }}" />
                <button type="submit" class="w-full px-2 py-1.5 rounded border border-red-200 text-red-600 text-xs font-semibold hover:bg-red-50">Hapus</button>
              </form>
            </div>
          @endforeach
        </div>
      @endif
      <input type="file" name="gallery[]" accept="image/jpeg,image/png" multiple class="w-full text-sm" />
      <p class="text-xs text-on-surface-variant mt-1">Gambar baru ditambahkan ke galeri. Gambar lama tidak ikut terhapus kecuali Anda menekan tombol Hapus.</p>
    </div>
  </div>

  <div class="bg-white border border-outline-variant rounded-lg p-5 card-shadow space-y-4">
    <h2 class="font-semibold text-primary">Publikasi</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-primary mb-1.5">Status</label>
        <select name="status" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">
          <option value="draft" @selected(old('status', $portfolio->status ?: 'draft') === 'draft')>Draft</option>
          <option value="publish" @selected(old('status', $portfolio->status) === 'publish')>Publish</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-primary mb-1.5">Urutan Tampil</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $portfolio->sort_order ?? 0) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
      </div>
    </div>
  </div>

  <div class="flex justify-end gap-2">
    <a href="{{ route('admin.portfolio.index') }}" class="px-5 py-2.5 border border-outline-variant rounded text-sm font-medium">Batal</a>
    <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded text-sm font-medium">Simpan</button>
  </div>
</form>
@endsection
