<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceRequestPublicController extends Controller
{
    /**
     * POST /api/requests
     * Endpoint publik (tanpa auth) — dipakai oleh Form Pesan Jasa.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],
            'website_type' => ['required', 'string', 'in:'.implode(',', ServiceRequest::WEBSITE_TYPES)],
            'description' => ['required', 'string'],
            'functional_requirements' => ['nullable', 'array'],
            'functional_requirements.*' => ['string'],
            'other_requirements' => ['nullable', 'string'],
        ]);

        $serviceRequest = ServiceRequest::create($validated + ['status' => 'Baru']);

        ActivityLog::record(
            'create',
            'service_requests',
            $serviceRequest->id,
            "Request baru masuk: {$serviceRequest->number} dari {$serviceRequest->company_name}"
        );

        return response()->json([
            'message' => 'Request berhasil dikirim.',
            'number' => $serviceRequest->number,
        ], 201);
    }
}
