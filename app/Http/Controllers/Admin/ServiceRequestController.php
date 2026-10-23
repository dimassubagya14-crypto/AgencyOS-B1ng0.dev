<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\RequestNote;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    // GET /api/admin/requests?status=Baru&q=sigma
    public function index(Request $request): JsonResponse
    {
        $query = ServiceRequest::query()->with('client:id,company_name')->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($q = $request->query('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('company_name', 'like', "%{$q}%")
                    ->orWhere('number', 'like', "%{$q}%")
                    ->orWhere('project_name', 'like', "%{$q}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    // GET /api/admin/requests/{id}
    public function show(ServiceRequest $request): JsonResponse
    {
        $request->load(['client', 'notes.user:id,name', 'assignee:id,name']);

        return response()->json($request);
    }

    // PATCH /api/admin/requests/{id}
    // Edit data client yang belum lengkap (PRD bab 6 — Request Management).
    public function update(Request $httpRequest, ServiceRequest $request): JsonResponse
    {
        $validated = $httpRequest->validate([
            'company_name' => ['sometimes', 'string', 'max:255'],
            'contact_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:30'],
            'email' => ['sometimes', 'email', 'max:255'],
            'project_name' => ['sometimes', 'string', 'max:255'],
            'website_type' => ['sometimes', 'string', 'in:'.implode(',', ServiceRequest::WEBSITE_TYPES)],
            'description' => ['sometimes', 'string'],
            'assigned_to' => ['sometimes', 'nullable', 'exists:users,id'],
        ]);

        $request->update($validated);

        ActivityLog::record('update', 'service_requests', $request->id, "Data request {$request->number} diperbarui");

        return response()->json($request->fresh());
    }

    // PATCH /api/admin/requests/{id}/status
    public function changeStatus(Request $httpRequest, ServiceRequest $request): JsonResponse
    {
        $validated = $httpRequest->validate([
            'status' => ['required', 'string', 'in:'.implode(',', ServiceRequest::STATUSES)],
        ]);

        $old = $request->status;
        $request->update(['status' => $validated['status']]);

        ActivityLog::record(
            'status_change',
            'service_requests',
            $request->id,
            "Status {$request->number} diubah dari \"{$old}\" ke \"{$validated['status']}\""
        );

        return response()->json($request->fresh());
    }

    // POST /api/admin/requests/{id}/notes
    public function addNote(Request $httpRequest, ServiceRequest $request): JsonResponse
    {
        $validated = $httpRequest->validate([
            'note' => ['required', 'string'],
        ]);

        $note = RequestNote::create([
            'service_request_id' => $request->id,
            'user_id' => $httpRequest->user()->id,
            'note' => $validated['note'],
        ]);

        ActivityLog::record('update', 'service_requests', $request->id, "Catatan ditambahkan pada {$request->number}");

        return response()->json($note->load('user:id,name'), 201);
    }

    // POST /api/admin/requests/{id}/convert-to-client
    // Business rule inti PRD bab 5: request & client dipisah, 1 client bisa
    // punya banyak request. Endpoint ini yang menjembatani keduanya.
    public function convertToClient(ServiceRequest $request): JsonResponse
    {
        if ($request->client_id) {
            return response()->json($request->load('client'));
        }

        $client = Client::firstOrCreate(
            ['company_name' => $request->company_name],
            [
                'contact_person' => $request->contact_name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]
        );

        $request->update(['client_id' => $client->id]);

        ActivityLog::record(
            $client->wasRecentlyCreated ? 'create' : 'update',
            'clients',
            $client->id,
            $client->wasRecentlyCreated
                ? "Client baru dibuat dari request {$request->number}: {$client->company_name}"
                : "Request {$request->number} dihubungkan ke client {$client->company_name}"
        );

        return response()->json($request->fresh('client'));
    }
}
