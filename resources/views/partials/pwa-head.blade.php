@php $isAdminPwa = ($pwaScope ?? 'public') === 'admin'; @endphp
<link rel="manifest" href="{{ $isAdminPwa ? route('pwa.manifest.admin') : route('pwa.manifest') }}" />
<meta name="theme-color" content="#0F172A" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-title" content="{{ $isAdminPwa ? 'AgencyOS' : 'B!ng0.dev' }}" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png" />
