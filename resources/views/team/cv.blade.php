@php
    $agencyProjects = $member->agencyProjects();
    $projects = array_values($member->projects ?? []);
    $certificates = array_values($member->certificates ?? []);
    $experiences = array_values($member->experiences ?? []);
    $education = array_values($member->education ?? []);
    $languages = array_values($member->languages ?? []);
    $social = $member->social_links ?? [];
    $socialLabels = ['github' => 'GitHub', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'website' => 'Website'];
    $skills = collect($member->skills ?? [])
        ->merge($agencyProjects->flatMap(fn ($p) => $p->technologies ?? [])->all())
        ->filter(fn ($t) => trim((string) $t) !== '')
        ->unique(fn ($t) => mb_strtolower(trim((string) $t)))
        ->values();
    $headline = $member->headline ?: $member->position;
    $about = $member->about ?: $member->bio;
    $contact = [];
    if ($member->show_contact) {
        if ($member->contactEmail()) { $contact[] = $member->contactEmail(); }
        if ($member->phone) { $contact[] = $member->phone; }
    }
    if ($member->location) { $contact[] = $member->location; }
    $portfolioUrl = $member->slug ? route('team.portfolio', $member->slug) : null;
    $backUrl = ! empty($preview) ? route('admin.team.portfolio.edit', $member) : ($portfolioUrl ?: route('home'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex" />
<title>CV — {{ $member->name }}</title>
@include('partials.favicon')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#e2e8f0;color:#0f172a;font-family:Inter,"Segoe UI",Arial,sans-serif;font-size:10.5pt;line-height:1.5}
  .bar{position:sticky;top:0;z-index:5;display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;justify-content:space-between;padding:.7rem 1rem;background:#0f172a;color:#fff;font-size:.85rem}
  .bar a{color:#cbd5e1;text-decoration:none}
  .bar a:hover{color:#fff}
  .bar button{padding:.5rem 1rem;border:0;border-radius:.4rem;background:#3b82f6;color:#fff;font:600 .85rem Inter,sans-serif;cursor:pointer}
  .bar button:hover{background:#2563eb}
  .hint{max-width:210mm;margin:.75rem auto 0;padding:0 1rem;color:#475569;font-size:.8rem}
  .page{max-width:210mm;margin:.75rem auto 2rem;padding:16mm 16mm 14mm;background:#fff;box-shadow:0 6px 28px rgba(15,23,42,.18)}
  h1{margin:0;font-size:22pt;line-height:1.15;letter-spacing:-.01em}
  .role{margin:.2rem 0 0;font-size:11.5pt;font-weight:600;color:#1d4ed8}
  .contact{margin:.45rem 0 0;color:#334155;font-size:9.5pt}
  .contact span+span::before{content:"  \2022  ";color:#94a3b8}
  .sec{margin-top:1.05rem;break-inside:avoid-page}
  .sec>h2{margin:0 0 .45rem;padding-bottom:.2rem;border-bottom:1.5px solid #0f172a;font-size:9.5pt;letter-spacing:.14em;text-transform:uppercase}
  .item{margin-bottom:.65rem;break-inside:avoid}
  .row{display:flex;justify-content:space-between;gap:1rem;align-items:baseline}
  .name{font-weight:600}
  .when{flex-shrink:0;color:#475569;font-size:9.5pt}
  .sub{color:#334155}
  .desc{margin:.2rem 0 0;white-space:pre-line}
  .muted{color:#475569;font-size:9.5pt}
  p{margin:0}
  @page{size:A4;margin:12mm}
  @media print{
    body{background:#fff;font-size:10pt}
    .bar,.hint{display:none}
    .page{margin:0;max-width:none;padding:0;box-shadow:none}
    a{color:inherit;text-decoration:none}
    *{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  }
</style>
</head>
<body>
<div class="bar">
  <a href="{{ $backUrl }}">&larr; {{ ! empty($preview) ? 'Kembali ke editor' : 'Kembali ke portofolio' }}</a>
  <button type="button" onclick="window.print()">Cetak / Simpan sebagai PDF</button>
</div>
<p class="hint">Tekan tombol di atas, lalu pilih <strong>Simpan sebagai PDF</strong> sebagai printer. Bagian bar ini tidak ikut tercetak.@if (! $member->show_contact) Email dan nomor telepon tidak ditampilkan; aktifkan opsi "Tampilkan email dan WhatsApp" di editor kalau ingin menyertakannya.@endif</p>

<div class="page">
  <header>
    <h1>{{ $member->name }}</h1>
    @if ($headline)<p class="role">{{ $headline }}</p>@endif
    @if (count($contact))
      <p class="contact">@foreach ($contact as $c)<span>{{ $c }}</span>@endforeach</p>
    @endif
    @php
        $links = [];
        foreach ($socialLabels as $key => $label) {
            if (! empty($social[$key])) { $links[] = $label.': '.$social[$key]; }
        }
        if ($portfolioUrl) { array_unshift($links, 'Portofolio: '.$portfolioUrl); }
    @endphp
    @if (count($links))
      <p class="contact">@foreach ($links as $l)<span>{{ $l }}</span>@endforeach</p>
    @endif
  </header>

  @if ($member->open_to_work && $member->desired_role)
    <section class="sec">
      <h2>Posisi yang Dicari</h2>
      <p>{{ $member->desired_role }}</p>
    </section>
  @endif

  @if ($about)
    <section class="sec">
      <h2>Ringkasan</h2>
      <p class="desc">{{ $about }}</p>
    </section>
  @endif

  @if (count($experiences))
    <section class="sec">
      <h2>Pengalaman Kerja</h2>
      @foreach ($experiences as $e)
        <div class="item">
          <div class="row">
            <div><span class="name">{{ $e['role'] }}</span>@if (! empty($e['company'])) <span class="sub">— {{ $e['company'] }}</span>@endif</div>
            @if (! empty($e['period']))<div class="when">{{ $e['period'] }}</div>@endif
          </div>
          @if (! empty($e['description']))<p class="desc">{{ $e['description'] }}</p>@endif
        </div>
      @endforeach
    </section>
  @endif

  @if ($skills->count())
    <section class="sec">
      <h2>Keahlian</h2>
      <p>{{ $skills->implode(', ') }}</p>
    </section>
  @endif

  @if ($agencyProjects->count() || count($projects))
    <section class="sec">
      <h2>Proyek</h2>
      @foreach ($agencyProjects as $p)
        <div class="item">
          <div class="row">
            <div><span class="name">{{ $p->name }}</span> <span class="sub">— {{ $p->category }}@if ($p->client_name), {{ $p->client_name }}@endif (B!ng0.dev)</span></div>
            @if ($p->project_year)<div class="when">{{ $p->project_year }}</div>@endif
          </div>
          <p class="desc">{{ Str::limit(strip_tags((string) ($p->tagline ?: $p->description)), 190) }}</p>
          @if (! empty($p->technologies))<p class="muted">Teknologi: {{ implode(', ', $p->technologies) }}</p>@endif
        </div>
      @endforeach
      @foreach ($projects as $pr)
        <div class="item">
          <div class="row">
            <div><span class="name">{{ $pr['title'] }}</span> <span class="sub">— Proyek pribadi</span></div>
            @if (! empty($pr['year']))<div class="when">{{ $pr['year'] }}</div>@endif
          </div>
          @if (! empty($pr['description']))<p class="desc">{{ $pr['description'] }}</p>@endif
          @if (! empty($pr['tech']))<p class="muted">Teknologi: {{ $pr['tech'] }}</p>@endif
          @if (! empty($pr['url']))<p class="muted">{{ $pr['url'] }}</p>@endif
        </div>
      @endforeach
    </section>
  @endif

  @if (count($education))
    <section class="sec">
      <h2>Pendidikan</h2>
      @foreach ($education as $ed)
        <div class="item">
          <div class="row">
            <div><span class="name">{{ $ed['school'] }}</span>@if (! empty($ed['degree'])) <span class="sub">— {{ $ed['degree'] }}</span>@endif</div>
            @if (! empty($ed['period']))<div class="when">{{ $ed['period'] }}</div>@endif
          </div>
        </div>
      @endforeach
    </section>
  @endif

  @if (count($certificates))
    <section class="sec">
      <h2>Sertifikat</h2>
      @foreach ($certificates as $c)
        <div class="item">
          <div class="row">
            <div><span class="name">{{ $c['name'] }}</span>@if (! empty($c['issuer'])) <span class="sub">— {{ $c['issuer'] }}</span>@endif</div>
            @if (! empty($c['year']))<div class="when">{{ $c['year'] }}</div>@endif
          </div>
        </div>
      @endforeach
    </section>
  @endif

  @if (count($languages))
    <section class="sec">
      <h2>Bahasa</h2>
      <p>{{ implode(', ', $languages) }}</p>
    </section>
  @endif
</div>
</body>
</html>
