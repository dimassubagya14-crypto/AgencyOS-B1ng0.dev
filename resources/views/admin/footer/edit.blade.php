@extends('layouts.admin')

@section('title', 'Edit Footer')

@section('content')
<div class="flex items-center justify-between gap-4 mb-6">
  <div>
    <h1 class="font-display font-bold text-2xl text-primary">Edit Footer</h1>
    <p class="text-sm text-on-surface-variant mt-1">Atur informasi yang tampil pada footer website publik.</p>
  </div>
</div>

<form method="POST" action="{{ route('admin.footer.update') }}" class="bg-white border border-outline-variant rounded-xl p-6 card-shadow max-w-3xl space-y-5">
  @csrf
  @method('PATCH')

  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Judul Footer</label>
    <input name="footer_title" value="{{ old('footer_title', $profile->footer_title ?: $profile->agency_name) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm" placeholder="B!ng0.dev" />
  </div>

  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Deskripsi Footer</label>
    <textarea name="footer_description" rows="4" class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm" placeholder="Deskripsi singkat agency...">{{ old('footer_description', $profile->footer_description ?: $profile->about) }}</textarea>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">WhatsApp</label>
      <input name="footer_whatsapp" value="{{ old('footer_whatsapp', $profile->footer_whatsapp ?: $profile->contact_phone) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm" placeholder="08123456789" />
      <p class="text-xs text-on-surface-variant mt-1.5">Akan dibuat link langsung ke WhatsApp.</p>
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Email</label>
      <input name="footer_email" type="email" value="{{ old('footer_email', $profile->footer_email ?: $profile->contact_email) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm" placeholder="hello@bing0.dev" />
      <p class="text-xs text-on-surface-variant mt-1.5">Akan dibuat link mailto.</p>
    </div>
  </div>

  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Alamat Footer</label>
    <textarea name="footer_address" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm">{{ old('footer_address', $profile->footer_address ?: $profile->contact_address) }}</textarea>
  </div>

  <div class="pt-2">
    <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-semibold">Simpan Footer</button>
  </div>
</form>
@endsection
