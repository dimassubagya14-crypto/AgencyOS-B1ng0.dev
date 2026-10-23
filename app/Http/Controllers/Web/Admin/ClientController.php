<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::query()->withCount('serviceRequests')->latest();

        if ($q = $request->query('q')) {
            $query->where('company_name', 'like', "%{$q}%");
        }

        return view('admin.clients.index', [
            'clients' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $client->update($validated);

        ActivityLog::record('update', 'clients', $client->id, "Data client {$client->company_name} diperbarui");

        return back()->with('success', 'Client diperbarui.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        ActivityLog::record('delete', 'clients', $client->id, "Client {$client->company_name} dihapus");

        return back()->with('success', 'Client dihapus.');
    }
}
