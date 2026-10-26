@extends('layouts.public')

@section('title', 'Pesan Jasa — B!ng0.dev')
@section('description', 'Isi form pesan jasa untuk memulai proyek website atau aplikasi custom Anda bersama B!ng0.dev.')

@section('content')
<section class="max-w-2xl mx-auto px-5 md:px-10 py-14">
  <h1 class="font-display font-bold text-2xl md:text-3xl text-primary mb-2">Pesan Jasa</h1>
  <p class="text-on-surface-variant mb-8">Isi form berikut, tim kami akan follow up secepatnya.</p>

  <form method="POST" action="{{ route('requests.store') }}" class="bg-white border border-outline-variant rounded-xl p-6 md:p-8 card-shadow space-y-5">
    @csrf
    {{-- Anti-bot: jangan diisi (disembunyikan dari pengunjung asli) --}}
    <div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
      <label for="website">Website</label>
      <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
    </div>

    <div>
      <h2 class="font-display font-semibold text-lg text-primary mb-4">Data Kontak</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-primary mb-1.5" for="company_name">Nama Kantor/Perusahaan</label>
          <input id="company_name" name="company_name" value="{{ old('company_name') }}" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm" />
          @error('company_name')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-primary mb-1.5" for="contact_name">Nama Pemesan (Contact Person)</label>
          <input id="contact_name" name="contact_name" value="{{ old('contact_name') }}" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm" />
          @error('contact_name')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-primary mb-1.5" for="phone">Nomor HP/WhatsApp</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm" />
            @error('phone')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-primary mb-1.5" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm" />
            @error('email')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
          </div>
        </div>
      </div>
    </div>

    <div class="border-t border-outline-variant pt-5">
      <h2 class="font-display font-semibold text-lg text-primary mb-4">Detail Proyek</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-primary mb-1.5" for="project_name">Nama Website/Aplikasi</label>
          <input id="project_name" name="project_name" value="{{ old('project_name') }}" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm" />
          @error('project_name')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-primary mb-1.5" for="website_type">Jenis Website</label>
          <select id="website_type" name="website_type" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm">
            <option value="">Pilih jenis website</option>
            @foreach ($websiteTypes as $t)
              <option value="{{ $t }}" @selected(old('website_type') === $t)>{{ $t }}</option>
            @endforeach
          </select>
          @error('website_type')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-primary mb-1.5" for="description">Deskripsi Kebutuhan</label>
          <textarea id="description" name="description" rows="4" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm">{{ old('description') }}</textarea>
          @error('description')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
        </div>
      </div>
    </div>

    <div class="border-t border-outline-variant pt-5">
      <h2 class="font-display font-semibold text-lg text-primary mb-1">Kebutuhan Fungsional</h2>
      <p class="text-sm text-on-surface-variant mb-4">Pilih fitur yang dibutuhkan (opsional).</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach ($functionalOptions as $key => $opt)
          <label class="cursor-pointer">
            <input type="checkbox" name="functional_requirements[]" value="{{ $key }}" class="peer hidden" @checked(in_array($key, old('functional_requirements', [])))>
            <div class="h-full border rounded-lg p-4 flex items-start gap-3 border-outline-variant peer-checked:border-secondary-bright peer-checked:bg-secondary-bright/5 peer-checked:ring-1 peer-checked:ring-secondary-bright">
              <span class="material-symbols-outlined text-on-surface-variant mt-0.5">{{ $opt['icon'] }}</span>
              <div><span class="block text-sm font-medium text-primary">{{ $opt['label'] }}</span><span class="block text-xs text-on-surface-variant">{{ $opt['desc'] }}</span></div>
            </div>
          </label>
        @endforeach
      </div>
      <div class="mt-4">
        <label class="block text-sm font-medium text-primary mb-1.5" for="other_requirements">Kebutuhan Lainnya (opsional)</label>
        <textarea id="other_requirements" name="other_requirements" rows="3" class="w-full bg-white border border-outline-variant rounded-lg px-4 py-2.5 text-sm">{{ old('other_requirements') }}</textarea>
      </div>
    </div>

    <div class="border-t border-outline-variant pt-6 flex justify-end">
      <button type="submit" class="px-6 py-2.5 bg-primary text-on-primary rounded font-medium hover:bg-inverse-surface">Kirim Request</button>
    </div>
  </form>
</section>
@endsection
