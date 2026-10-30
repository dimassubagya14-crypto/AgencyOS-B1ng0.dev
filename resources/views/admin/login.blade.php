<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Admin Login — B!ng0.dev</title>
<meta name="robots" content="noindex, nofollow" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="/css/app.css" />
</head>
<body class="font-body text-on-surface antialiased">
@include('partials.page-loader', ['loaderMin' => 450, 'loaderMinRepeat' => 150])
<div class="min-h-screen flex items-center justify-center bg-surface-container-low px-5">
  <div class="w-full max-w-sm bg-white border border-outline-variant rounded-xl p-8 card-shadow">
    <div class="font-display font-bold text-xl text-primary mb-1">B!ng0<span class="text-secondary-bright">.dev</span></div>
    <p class="text-sm text-on-surface-variant mb-6">Secure Admin Access</p>

    @if ($errors->any())
      <div class="bg-error-container text-on-error-container px-4 py-3 rounded-lg text-sm mb-4">{{ $errors->first() }}</div>
    @endif
    @if (session('success'))
      <div class="bg-success-container text-success px-4 py-3 rounded-lg text-sm mb-4">{{ session('success') }}</div>
    @endif

    @if (($step ?? 'password') === 'password')
      <div class="text-xs text-on-surface-variant mb-4">Langkah 1 dari 3 · Verifikasi password</div>
      <form method="POST" action="{{ route('admin.login.password') }}">
        @csrf
        <div class="mb-5">
          <label class="block text-sm font-medium text-primary mb-1.5" for="password">Password</label>
          <input id="password" name="password" type="password" required autofocus autocomplete="current-password" class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm" />
        </div>
        <button type="submit" class="w-full bg-primary text-on-primary py-2.5 rounded font-medium">Lanjut</button>
      </form>
    @elseif ($step === 'email')
      <div class="text-xs text-on-surface-variant mb-4">Langkah 2 dari 3 · Masukkan email akun admin</div>
      <form method="POST" action="{{ route('admin.login.email') }}">
        @csrf
        <div class="mb-5">
          <label class="block text-sm font-medium text-primary mb-1.5" for="email">Email</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="akun@email.nya" class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm" />
        </div>
        <button type="submit" class="w-full bg-primary text-on-primary py-2.5 rounded font-medium">Kirim OTP</button>
      </form>
      <form method="POST" action="{{ route('admin.login.reset') }}" class="mt-3">@csrf<button class="w-full text-xs text-on-surface-variant">← Kembali</button></form>
    @else
      <div class="text-xs text-on-surface-variant mb-4">Langkah 3 dari 3 · OTP dikirim ke <strong>{{ $otpEmail }}</strong></div>
      <form method="POST" action="{{ route('admin.login.otp') }}">
        @csrf
        <div class="mb-5">
          <label class="block text-sm font-medium text-primary mb-1.5" for="otp">OTP 6 digit</label>
          <input id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm tracking-[0.35em] text-center" />
          <p class="text-[11px] text-on-surface-variant mt-2">OTP berlaku 5 menit dan maksimal 5 percobaan.</p>
        </div>
        <button type="submit" class="w-full bg-primary text-on-primary py-2.5 rounded font-medium">Verifikasi & Masuk</button>
      </form>
      <form method="POST" action="{{ route('admin.login.resend') }}" class="mt-3">@csrf<button class="w-full text-xs text-secondary">Kirim OTP baru</button></form>
    @endif

    <a href="{{ route('home') }}" class="block text-center text-xs text-on-surface-variant mt-4 hover:text-primary">← Kembali ke website</a>
  </div>
</div>
</body>
</html>
