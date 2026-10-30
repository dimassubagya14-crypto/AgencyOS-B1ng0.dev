<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Portfolio;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamPortfolioController extends Controller
{
    public function edit(TeamMember $member): View
    {
        return view('admin.team.portfolio', [
            'member' => $member,
            'agencyPortfolios' => Portfolio::published()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, TeamMember $member): RedirectResponse
    {
        $url = ['nullable', 'url:http,https', 'max:255'];

        $data = $request->validate([
            'headline' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:120'],
            'about' => ['nullable', 'string', 'max:5000'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'social' => ['nullable', 'array'],
            'social.github' => $url,
            'social.linkedin' => $url,
            'social.instagram' => $url,
            'social.website' => $url,
            'certificates' => ['nullable', 'array', 'max:20'],
            'certificates.*.name' => ['nullable', 'string', 'max:160'],
            'certificates.*.issuer' => ['nullable', 'string', 'max:160'],
            'certificates.*.year' => ['nullable', 'string', 'max:10'],
            'certificates.*.url' => $url,
            'projects' => ['nullable', 'array', 'max:20'],
            'projects.*.title' => ['nullable', 'string', 'max:160'],
            'projects.*.description' => ['nullable', 'string', 'max:1000'],
            'projects.*.tech' => ['nullable', 'string', 'max:200'],
            'projects.*.year' => ['nullable', 'string', 'max:10'],
            'projects.*.url' => $url,
            'agency_portfolio_ids' => ['nullable', 'array'],
            'agency_portfolio_ids.*' => ['integer', 'exists:portfolios,id'],
        ]);

        $text = fn ($value) => trim((string) $value);

        $certificates = collect($data['certificates'] ?? [])
            ->map(fn ($c) => [
                'name' => $text($c['name'] ?? ''),
                'issuer' => $text($c['issuer'] ?? ''),
                'year' => $text($c['year'] ?? ''),
                'url' => $text($c['url'] ?? ''),
            ])
            ->filter(fn ($c) => $c['name'] !== '')
            ->values()
            ->all();

        $projects = collect($data['projects'] ?? [])
            ->map(fn ($p) => [
                'title' => $text($p['title'] ?? ''),
                'description' => $text($p['description'] ?? ''),
                'tech' => $text($p['tech'] ?? ''),
                'year' => $text($p['year'] ?? ''),
                'url' => $text($p['url'] ?? ''),
            ])
            ->filter(fn ($p) => $p['title'] !== '')
            ->values()
            ->all();

        $social = collect($data['social'] ?? [])
            ->map(fn ($v) => $text($v))
            ->filter()
            ->all();

        $skills = isset($data['skills'])
            ? array_values(array_filter(array_map('trim', explode(',', $data['skills']))))
            : [];

        $ids = array_values(array_unique(array_map('intval', $data['agency_portfolio_ids'] ?? [])));

        $member->fill([
            'headline' => $data['headline'] ?? null,
            'location' => $data['location'] ?? null,
            'about' => $data['about'] ?? null,
            'skills' => $skills,
            'social_links' => $social,
            'certificates' => $certificates,
            'projects' => $projects,
            'agency_portfolio_ids' => $ids,
            'show_contact' => $request->boolean('show_contact'),
        ])->save();

        ActivityLog::record('update', 'team_members', $member->id, "Portofolio pribadi {$member->name} diperbarui");

        return back()->with('success', 'Portofolio pribadi disimpan.');
    }

    // Buat otomatis: slug + isi awal dari data yang sudah ada + proyek dari portfolio agency.
    public function generate(TeamMember $member): RedirectResponse
    {
        $member->ensureSlug();
        $member->headline = $member->headline ?: $member->position;
        $member->about = $member->about ?: $member->bio;

        if (empty($member->agency_portfolio_ids)) {
            $member->agency_portfolio_ids = Portfolio::published()->pluck('id')->all();
        }

        $member->portfolio_published = true;
        $member->save();

        ActivityLog::record('update', 'team_members', $member->id, "Portofolio pribadi {$member->name} dibuat dan dipublikasikan");

        return redirect()->route('admin.team.portfolio.edit', $member)
            ->with('success', 'Portofolio pribadi dibuat dan dipublikasikan. Lengkapi datanya di bawah.');
    }

    public function togglePublish(TeamMember $member): RedirectResponse
    {
        if (! $member->slug) {
            return back()->withErrors(['portfolio' => 'Buat portofolionya dulu dengan tombol "Buat Portofolio Otomatis".']);
        }

        $member->update(['portfolio_published' => ! $member->portfolio_published]);

        ActivityLog::record('update', 'team_members', $member->id, "Portofolio pribadi {$member->name} ".($member->portfolio_published ? 'dipublikasikan' : 'disembunyikan'));

        return back()->with('success', $member->portfolio_published ? 'Portofolio dipublikasikan.' : 'Portofolio disembunyikan dari publik.');
    }

    public function preview(TeamMember $member): View
    {
        return view('team.portfolio', ['member' => $member, 'preview' => true]);
    }
}
