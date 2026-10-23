@extends('layouts.admin')

@section('title', 'Company Profile')

@section('content')
<h1 class="font-display font-bold text-2xl text-primary mb-6">Company Profile</h1>
<form method="POST" action="{{ route('admin.company-profile.update') }}" class="bg-white border border-outline-variant rounded-lg p-6 card-shadow max-w-2xl space-y-4">
  @csrf
  @method('PATCH')
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Nama Agency</label>
    <input name="agency_name" value="{{ old('agency_name', $profile->agency_name) }}" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">About</label>
    <textarea name="about" rows="3" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('about', $profile->about) }}</textarea>
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Visi & Misi</label>
    <textarea name="vision_mission" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('vision_mission', $profile->vision_mission) }}</textarea>
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Services</label>
    <textarea name="services" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('services', $profile->services) }}</textarea>
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Advantages</label>
    <textarea name="advantages" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('advantages', $profile->advantages) }}</textarea>
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Process</label>
    <textarea name="process" rows="2" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm">{{ old('process', $profile->process) }}</textarea>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Email Kontak</label>
      <input name="contact_email" type="email" value="{{ old('contact_email', $profile->contact_email) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-primary mb-1.5">Telepon Kontak</label>
      <input name="contact_phone" value="{{ old('contact_phone', $profile->contact_phone) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
    </div>
  </div>
  <div>
    <label class="block text-sm font-medium text-primary mb-1.5">Alamat</label>
    <input name="contact_address" value="{{ old('contact_address', $profile->contact_address) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm" />
  </div>
  <div class="flex flex-wrap items-center gap-3 pt-2">
  <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded text-sm font-medium">Simpan Perubahan</button>
  <a href="{{ route('admin.footer.edit') }}" class="px-5 py-2.5 border border-outline-variant rounded-lg text-sm font-medium text-primary">Edit Footer</a>
</div>
</form>
@endsection
