<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    // GET /api/admin/portfolios
    public function index(Request $request): JsonResponse
    {
        $query = Portfolio::query()->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json($query->paginate(20));
    }

    // POST /api/admin/portfolios
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->storeImage($request->file('thumbnail'));
        }

        $portfolio = Portfolio::create($validated);

        ActivityLog::record('create', 'portfolios', $portfolio->id, "Portfolio baru dibuat: {$portfolio->name}");

        return response()->json($portfolio, 201);
    }

    // PATCH /api/admin/portfolios/{id}
    public function update(Request $request, Portfolio $portfolio): JsonResponse
    {
        $validated = $this->validated($request, updating: true);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->storeImage($request->file('thumbnail'));
        }

        $portfolio->update($validated);

        ActivityLog::record('update', 'portfolios', $portfolio->id, "Portfolio \"{$portfolio->name}\" diperbarui");

        return response()->json($portfolio->fresh());
    }

    // PATCH /api/admin/portfolios/{id}/publish
    public function togglePublish(Portfolio $portfolio): JsonResponse
    {
        $portfolio->status = $portfolio->status === 'publish' ? 'draft' : 'publish';
        $portfolio->save();

        ActivityLog::record('update', 'portfolios', $portfolio->id, "Portfolio \"{$portfolio->name}\" status diubah ke {$portfolio->status}");

        return response()->json($portfolio);
    }

    // DELETE /api/admin/portfolios/{id}
    public function destroy(Portfolio $portfolio): JsonResponse
    {
        $portfolio->delete();

        ActivityLog::record('delete', 'portfolios', $portfolio->id, "Portfolio \"{$portfolio->name}\" dihapus");

        return response()->json(['message' => 'Portfolio dihapus.']);
    }

    private function validated(Request $request, bool $updating = false): array
    {
        $rule = $updating ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$rule, 'string', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'category' => [$rule, 'string', 'max:100'],
            'description' => [$rule, 'string'],
            'problem' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'technologies' => ['nullable', 'array'],
            'url' => ['nullable', 'url', 'max:255'],
            'status' => ['nullable', 'in:draft,publish'],
            'sort_order' => ['nullable', 'integer'],
            'thumbnail' => ['nullable', 'image', 'max:4096'], // 4MB, sesuai NFR pembatasan upload
        ]);
    }

    private function storeImage($file): string
    {
        // Disimpan di storage/app/public/portfolios — jalankan `php artisan storage:link`.
        return $file->store('portfolios', 'public');
    }
}
