<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.company-profile.edit', ['profile' => CompanyProfile::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agency_name' => ['required', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'vision_mission' => ['nullable', 'string'],
            'services' => ['nullable', 'string'],
            'advantages' => ['nullable', 'string'],
            'process' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_address' => ['nullable', 'string'],
            'footer_title' => ['nullable', 'string', 'max:255'],
            'footer_description' => ['nullable', 'string'],
            'footer_whatsapp' => ['nullable', 'string', 'max:30'],
            'footer_email' => ['nullable', 'email'],
            'footer_address' => ['nullable', 'string', 'max:500'],
        ]);

        $profile = CompanyProfile::current();
        $profile->update($validated);

        ActivityLog::record('update', 'company_profiles', $profile->id, 'Company profile diperbarui');

        return back()->with('success', 'Company profile disimpan.');
    }
    public function footerEdit(): View
    {
        return view('admin.footer.edit', ['profile' => CompanyProfile::current()]);
    }

    public function footerUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'footer_title' => ['nullable', 'string', 'max:255'],
            'footer_description' => ['nullable', 'string'],
            'footer_whatsapp' => ['nullable', 'string', 'max:30'],
            'footer_email' => ['nullable', 'email'],
            'footer_address' => ['nullable', 'string', 'max:500'],
        ]);

        $profile = CompanyProfile::current();
        $profile->update($validated);

        ActivityLog::record('update', 'company_profiles', $profile->id, 'Footer website diperbarui');

        return back()->with('success', 'Footer berhasil diperbarui.');
    }

}
