<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // GET /api/admin/clients?q=sigma
    public function index(Request $request): JsonResponse
    {
        $query = Client::query()->withCount('serviceRequests')->latest();

        if ($q = $request->query('q')) {
            $query->where('company_name', 'like', "%{$q}%");
        }

        return response()->json($query->paginate(20));
    }

    // POST /api/admin/clients
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $client = Client::create($validated);

        ActivityLog::record('create', 'clients', $client->id, "Client baru dibuat manual: {$client->company_name}");

        return response()->json($client, 201);
    }

    // GET /api/admin/clients/{id}
    public function show(Client $client): JsonResponse
    {
        $client->load(['serviceRequests' => fn ($q) => $q->latest()->limit(20)]);

        return response()->json($client);
    }

    // PATCH /api/admin/clients/{id}
    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['sometimes', 'string', 'max:255'],
            'contact_person' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'internal_notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $client->update($validated);

        ActivityLog::record('update', 'clients', $client->id, "Data client {$client->company_name} diperbarui");

        return response()->json($client->fresh());
    }

    // DELETE /api/admin/clients/{id}
    public function destroy(Client $client): JsonResponse
    {
        $client->delete(); // soft delete

        ActivityLog::record('delete', 'clients', $client->id, "Client {$client->company_name} dihapus");

        return response()->json(['message' => 'Client dihapus.']);
    }
}
