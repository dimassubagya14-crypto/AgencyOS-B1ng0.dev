<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\RequestNote;
use App\Models\ServiceRequest;
use App\Models\TeamMember;
use App\Mail\TeamAssignmentMail;
use App\Services\TeamAssignmentMessageService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
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
            'teamMembers' => TeamMember::suggestedFor($request->website_type),
            'assignedTeamMembers' => $request->teamMembers,
            'assignmentMessages' => $request->teamMembers->mapWithKeys(function (TeamMember $member) use ($request) {
                $messages = app(TeamAssignmentMessageService::class)->render(
                    $request, $member, $member->pivot->task, $member->pivot->deadline
                );
                return [$member->id => $messages];
            }),
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

    // Membagi request ke satu atau beberapa anggota tim, lengkap dengan tugas/deadline.
    public function assignTeam(Request $httpRequest, ServiceRequest $request): RedirectResponse
    {
        $validated = $httpRequest->validate([
            'team_member_ids' => ['nullable', 'array'],
            'team_member_ids.*' => ['exists:team_members,id'],
            'tasks' => ['nullable', 'array'],
            'tasks.*' => ['nullable', 'string', 'max:5000'],
            'deadlines' => ['nullable', 'array'],
            'deadlines.*' => ['nullable', 'date'],
            'task_statuses' => ['nullable', 'array'],
            'task_statuses.*' => ['nullable', 'in:assigned,in_progress,completed'],
        ]);

        $ids = $validated['team_member_ids'] ?? [];
        $tasks = $validated['tasks'] ?? [];
        $deadlines = $validated['deadlines'] ?? [];
        $statuses = $validated['task_statuses'] ?? [];
        $sync = [];

        foreach ($ids as $id) {
            $sync[$id] = [
                'task' => trim((string) ($tasks[$id] ?? '')),
                'deadline' => $deadlines[$id] ?? null,
                'task_status' => $statuses[$id] ?? 'assigned',
            ];
        }

        $request->teamMembers()->sync($sync);

        $names = TeamMember::whereIn('id', $ids)->pluck('name')->implode(', ');

        ActivityLog::record(
            'update',
            'service_requests',
            $request->id,
            $ids
                ? "Pembagian tugas {$request->number} disimpan untuk: {$names}"
                : "Pembagian tugas untuk {$request->number} dikosongkan"
        );

        return back()->with('success', 'Pembagian tugas berhasil disimpan.');
    }

    public function assignmentMessage(Request $httpRequest, ServiceRequest $request, TeamMember $member): JsonResponse
    {
        abort_unless($request->teamMembers()->whereKey($member->id)->exists(), 404);

        $assignment = $request->teamMembers()->whereKey($member->id)->firstOrFail();
        $messages = app(TeamAssignmentMessageService::class)->render(
            $request,
            $member,
            $assignment->pivot->task,
            $assignment->pivot->deadline
        );

        return response()->json([
            'member' => $member->name,
            'email' => $member->email ?: optional($member->user)->email,
            'phone' => $member->phone,
            'whatsapp' => $messages['whatsapp'],
            'email_message' => $messages['email'],
        ]);
    }

    public function sendAssignmentEmail(Request $httpRequest, ServiceRequest $request, TeamMember $member): RedirectResponse
    {
        abort_unless($request->teamMembers()->whereKey($member->id)->exists(), 404);

        $email = $member->email ?: optional($member->user)->email;
        if (! $email) {
            return back()->withErrors(['email' => "Email {$member->name} belum diisi pada data anggota tim."]);
        }

        $assignment = $request->teamMembers()->whereKey($member->id)->firstOrFail();
        $messages = app(TeamAssignmentMessageService::class)->render(
            $request, $member, $assignment->pivot->task, $assignment->pivot->deadline
        );
        $content = $httpRequest->validate([
            'content' => ['nullable', 'string', 'max:10000'],
        ])['content'] ?? $messages['email'];

        try {
            Mail::to($email)->send(new TeamAssignmentMail($request, $member, $content));
        } catch (\Throwable $e) {
            report($e);
            ActivityLog::record('notification_failed', 'service_requests', $request->id, "Email pembagian tugas {$request->number} ke {$member->name} gagal dikirim");

            return back()->withErrors(['email' => "Email ke {$member->name} gagal dikirim. Periksa pengaturan email (Resend) atau alamat email anggota tim, lalu coba lagi."]);
        }

        $request->teamMembers()->updateExistingPivot($member->id, ['email_sent_at' => now()]);
        ActivityLog::record('notification', 'service_requests', $request->id, "Email pembagian tugas {$request->number} dikirim ke {$member->name}");

        return back()->with('success', "Email pembagian tugas dikirim ke {$member->name}.");
    }

    public function assignmentWhatsapp(Request $httpRequest, ServiceRequest $request, TeamMember $member): RedirectResponse
    {
        abort_unless($request->teamMembers()->whereKey($member->id)->exists(), 404);

        $phone = preg_replace('/\D+/', '', (string) $member->phone);
        if ($phone === '') {
            return back()->withErrors(['phone' => "Nomor WhatsApp {$member->name} belum diisi pada data anggota tim."]);
        }

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        $assignment = $request->teamMembers()->whereKey($member->id)->firstOrFail();
        $messages = app(TeamAssignmentMessageService::class)->render(
            $request, $member, $assignment->pivot->task, $assignment->pivot->deadline
        );
        $content = $httpRequest->validate([
            'content' => ['nullable', 'string', 'max:10000'],
        ])['content'] ?? $messages['whatsapp'];

        $request->teamMembers()->updateExistingPivot($member->id, ['whatsapp_opened_at' => now()]);
        ActivityLog::record('notification', 'service_requests', $request->id, "Template WhatsApp pembagian tugas dibuka untuk {$member->name}");

        return redirect()->away('https://wa.me/' . $phone . '?text=' . rawurlencode($content));
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
