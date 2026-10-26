<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\RequestNote;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    public function create(): View
    {
        $functionalOptions = [
            'auth' => ['label' => 'Login & Hak Akses', 'desc' => 'Login, role, dan pembatasan akses.', 'icon' => 'lock'],
            'dashboard' => ['label' => 'Dashboard', 'desc' => 'Ringkasan data dan statistik.', 'icon' => 'dashboard'],
            'crud' => ['label' => 'CRUD Data', 'desc' => 'Tambah, ubah, hapus, dan kelola data.', 'icon' => 'database'],
            'payment' => ['label' => 'Pembayaran Online', 'desc' => 'Payment gateway, QRIS, dan transaksi.', 'icon' => 'payments'],
            'notification' => ['label' => 'Notifikasi', 'desc' => 'Email, WhatsApp, dan notifikasi sistem.', 'icon' => 'notifications'],
            'report' => ['label' => 'Laporan & Export', 'desc' => 'Laporan, cetak, dan export data.', 'icon' => 'description'],
            'search' => ['label' => 'Pencarian & Filter', 'desc' => 'Pencarian, filter, dan sortir data.', 'icon' => 'search'],
            'api' => ['label' => 'API / Integrasi', 'desc' => 'Integrasi layanan atau sistem eksternal.', 'icon' => 'api'],
        ];

        return view('requests.create', [
            'websiteTypes' => ServiceRequest::WEBSITE_TYPES,
            'functionalOptions' => $functionalOptions,
        ]);
    }

    public function store(Request $httpRequest): RedirectResponse
    {
        // Anti-bot: kolom "website" disembunyikan dari pengunjung asli. Kalau terisi, anggap spam.
        if ($httpRequest->filled('website')) {
            return redirect()->route('requests.success')
                ->with('request_number', 'REQ-'.now()->format('Ymd').'-'.random_int(1000, 9999));
        }

        $validated = $httpRequest->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],
            'website_type' => ['required', 'string', 'in:'.implode(',', ServiceRequest::WEBSITE_TYPES)],
            'description' => ['required', 'string'],
            'functional_requirements' => ['nullable', 'array'],
            'functional_requirements.*' => ['string', 'max:100'],
            'other_requirements' => ['nullable', 'string'],
        ]);

        $serviceRequest = ServiceRequest::create($validated + ['status' => 'Baru']);

        ActivityLog::record(
            'create',
            'service_requests',
            $serviceRequest->id,
            "Request baru masuk: {$serviceRequest->number} dari {$serviceRequest->company_name}"
        );

        return redirect()->route('requests.success')->with('request_number', $serviceRequest->number);
    }

    public function success(Request $httpRequest): View|RedirectResponse
    {
        $number = $httpRequest->session()->get('request_number');

        if (! $number) {
            return redirect()->route('requests.create');
        }

        return view('requests.success', ['number' => $number]);
    }

    public function index(Request $request): View
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

        return view('admin.requests.index', [
            'requests' => $query->paginate(20)->withQueryString(),
            'statuses' => ServiceRequest::STATUSES,
        ]);
    }

    public function show(ServiceRequest $request): View
    {
        $request->load(['client', 'notes.user:id,name', 'assignee:id,name', 'teamMembers']);

        return view('admin.requests.show', [
            'request' => $request,
            'statuses' => ServiceRequest::STATUSES,
            'teamMembers' => \App\Models\TeamMember::suggestedFor($request->website_type),
        ]);
    }

    public function updateStatus(Request $httpRequest, ServiceRequest $request): RedirectResponse
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

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    public function storeNote(Request $httpRequest, ServiceRequest $request): RedirectResponse
    {
        $validated = $httpRequest->validate(['note' => ['required', 'string']]);

        RequestNote::create([
            'service_request_id' => $request->id,
            'user_id' => $httpRequest->user()->id,
            'note' => $validated['note'],
        ]);

        ActivityLog::record('update', 'service_requests', $request->id, "Catatan ditambahkan pada {$request->number}");

        return back()->with('success', 'Catatan ditambahkan.');
    }

    // Nentuin siapa dari tim yang garap request ini (bisa lebih dari satu orang).
    public function assignTeam(Request $httpRequest, ServiceRequest $request): RedirectResponse
    {
        $validated = $httpRequest->validate([
            'team_member_ids' => ['nullable', 'array'],
            'team_member_ids.*' => ['exists:team_members,id'],
        ]);

        $ids = $validated['team_member_ids'] ?? [];
        $request->teamMembers()->sync($ids);

        $names = \App\Models\TeamMember::whereIn('id', $ids)->pluck('name')->implode(', ');

        ActivityLog::record(
            'update',
            'service_requests',
            $request->id,
            $ids
                ? "Request {$request->number} ditugaskan ke: {$names}"
                : "Penugasan tim untuk {$request->number} dikosongkan"
        );

        return back()->with('success', 'Penugasan tim disimpan.');
    }

    public function convertToClient(ServiceRequest $request): RedirectResponse
    {
        if (! $request->client_id) {
            $client = Client::firstOrCreate(
                ['company_name' => $request->company_name],
                ['contact_person' => $request->contact_name, 'phone' => $request->phone, 'email' => $request->email]
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
        }

        return back()->with('success', 'Client berhasil dibuat/dihubungkan.');
    }
}
