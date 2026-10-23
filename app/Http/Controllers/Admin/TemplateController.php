<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    // GET /api/admin/templates?type=proposal
    public function index(Request $request): JsonResponse
    {
        $query = Template::query()->latest();

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        return response()->json($query->get());
    }

    // POST /api/admin/templates
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:proposal,email,whatsapp,follow_up,confirmation'],
            'category' => ['nullable', 'string', 'max:100'],
            'content' => ['required', 'string'],
        ]);

        $template = Template::create($validated + ['is_active' => true]);

        ActivityLog::record('create', 'templates', $template->id, "Template baru dibuat: {$template->name}");

        return response()->json($template, 201);
    }

    // PATCH /api/admin/templates/{id}
    public function update(Request $request, Template $template): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:proposal,email,whatsapp,follow_up,confirmation'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'content' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $template->update($validated);

        ActivityLog::record('update', 'templates', $template->id, "Template \"{$template->name}\" diperbarui");

        return response()->json($template->fresh());
    }

    // DELETE /api/admin/templates/{id}
    public function destroy(Template $template): JsonResponse
    {
        $template->delete();

        ActivityLog::record('delete', 'templates', $template->id, "Template \"{$template->name}\" dihapus");

        return response()->json(['message' => 'Template dihapus.']);
    }
}
