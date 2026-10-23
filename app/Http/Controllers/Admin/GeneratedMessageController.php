<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GeneratedMessage;
use App\Models\ServiceRequest;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneratedMessageController extends Controller
{
    // POST /api/admin/requests/{request}/generate-message
    // Admin pilih template -> sistem isi placeholder -> preview dikembalikan
    // ke frontend untuk diedit sebelum disimpan (PRD bab 7).
    public function preview(Request $httpRequest, ServiceRequest $serviceRequest): JsonResponse
    {
        $validated = $httpRequest->validate([
            'template_id' => ['required', 'exists:templates,id'],
        ]);

        $template = Template::findOrFail($validated['template_id']);

        return response()->json([
            'content' => $template->renderFor($serviceRequest),
        ]);
    }

    // POST /api/admin/requests/{request}/messages
    // Simpan versi proposal/pesan setelah admin preview & edit.
    public function store(Request $httpRequest, ServiceRequest $serviceRequest): JsonResponse
    {
        $validated = $httpRequest->validate([
            'template_id' => ['nullable', 'exists:templates,id'],
            'channel' => ['required', 'in:email,whatsapp,manual'],
            'content' => ['required', 'string'],
        ]);

        $message = GeneratedMessage::create($validated + [
            'service_request_id' => $serviceRequest->id,
            'created_by' => $httpRequest->user()->id,
            'status' => 'draft',
        ]);

        ActivityLog::record(
            'create',
            'generated_messages',
            $message->id,
            "Pesan/proposal dibuat untuk request {$serviceRequest->number}"
        );

        return response()->json($message, 201);
    }

    // PATCH /api/admin/messages/{id}/mark-sent
    public function markSent(GeneratedMessage $message): JsonResponse
    {
        $message->update(['status' => 'sent', 'sent_at' => now()]);

        ActivityLog::record(
            'update',
            'generated_messages',
            $message->id,
            "Pesan untuk request #{$message->service_request_id} ditandai terkirim via {$message->channel}"
        );

        return response()->json($message->fresh());
    }
}
