<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CompanyProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    // GET /api/admin/company-profile
    public function show(): JsonResponse
    {
        return response()->json(CompanyProfile::current());
    }

    // PATCH /api/admin/company-profile
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agency_name' => ['sometimes', 'string', 'max:255'],
            'about' => ['sometimes', 'nullable', 'string'],
            'vision_mission' => ['sometimes', 'nullable', 'string'],
            'services' => ['sometimes', 'nullable', 'string'],
            'advantages' => ['sometimes', 'nullable', 'string'],
            'process' => ['sometimes', 'nullable', 'string'],
            'contact_email' => ['sometimes', 'nullable', 'email'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'contact_address' => ['sometimes', 'nullable', 'string'],
            'footer_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'footer_description' => ['sometimes', 'nullable', 'string'],
            'footer_whatsapp' => ['sometimes', 'nullable', 'string', 'max:30'],
            'footer_email' => ['sometimes', 'nullable', 'email'],
            'footer_address' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $profile = CompanyProfile::current();
        $profile->update($validated);

        ActivityLog::record('update', 'company_profiles', $profile->id, 'Company profile diperbarui');

        return response()->json($profile->fresh());
    }
}
