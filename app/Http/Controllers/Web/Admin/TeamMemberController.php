<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function index(): View
    {
        return view('admin.team.index', [
            'members' => TeamMember::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.team.form', ['member' => new TeamMember()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated = $this->handleUpload($request, $validated);

        $member = TeamMember::create($validated);

        ActivityLog::record('create', 'team_members', $member->id, "Anggota tim baru ditambahkan: {$member->name}");

        return redirect()->route('admin.team.index')->with('success', 'Anggota tim berhasil ditambahkan.');
    }

    public function edit(TeamMember $member): View
    {
        return view('admin.team.form', compact('member'));
    }

    public function update(Request $request, TeamMember $member): RedirectResponse
    {
        $validated = $this->validated($request, updating: true);
        $validated = $this->handleUpload($request, $validated);

        $member->update($validated);

        ActivityLog::record('update', 'team_members', $member->id, "Data anggota tim \"{$member->name}\" diperbarui");

        return redirect()->route('admin.team.index')->with('success', 'Anggota tim diperbarui.');
    }

    public function destroy(TeamMember $member): RedirectResponse
    {
        $member->delete();

        ActivityLog::record('delete', 'team_members', $member->id, "Anggota tim \"{$member->name}\" dihapus");

        return back()->with('success', 'Anggota tim dihapus.');
    }

    private function validated(Request $request, bool $updating = false): array
    {
        $rule = $updating ? 'sometimes' : 'required';

        $data = $request->validate([
            'name' => [$rule, 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'skills' => ['nullable', 'string'], // comma separated dari form
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['skills'] = isset($data['skills'])
            ? array_values(array_filter(array_map('trim', explode(',', $data['skills']))))
            : [];
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function handleUpload(Request $request, array $data): array
    {
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('team', 'public');
        }

        return $data;
    }
}
