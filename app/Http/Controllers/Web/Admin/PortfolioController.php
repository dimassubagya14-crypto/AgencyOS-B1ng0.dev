<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Portfolio;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\PortfolioPdfService;
use App\Services\PortfolioPowerPointService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $query = Portfolio::query()->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return view('admin.portfolio.index', [
            'portfolios' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.portfolio.form', [
            'portfolio' => new Portfolio(),
            'websiteTypes' => ServiceRequest::WEBSITE_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated = $this->handleUploads($request, $validated);

        $portfolio = Portfolio::create($validated);

        ActivityLog::record('create', 'portfolios', $portfolio->id, "Portfolio baru dibuat: {$portfolio->name}");

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio berhasil dibuat. Halaman microsite-nya sudah live di /portfolio/'.$portfolio->slug);
    }

    public function pdf(Portfolio $portfolio, PortfolioPdfService $pdfService)
    {
        ActivityLog::record('export', 'portfolios', $portfolio->id, "PDF portfolio \"{$portfolio->name}\" dibuat");

        return $pdfService->download($portfolio);
    }

    public function powerpoint(Portfolio $portfolio, PortfolioPowerPointService $powerPointService)
    {
        ActivityLog::record('export', 'portfolios', $portfolio->id, "PowerPoint portfolio \"{$portfolio->name}\" dibuat");

        return $powerPointService->download($portfolio);
    }

    public function exportSelectedPdf(Request $request, PortfolioPdfService $pdfService)
    {
        $portfolios = $this->selectedPortfolios($request);
        ActivityLog::record('export', 'portfolios', null, 'Gabungan PDF dibuat dari ' . $portfolios->count() . ' portfolio: ' . $portfolios->pluck('name')->implode(', '));
        return $pdfService->downloadMany($portfolios);
    }

    public function exportSelectedPowerPoint(Request $request, PortfolioPowerPointService $powerPointService)
    {
        $portfolios = $this->selectedPortfolios($request);
        ActivityLog::record('export', 'portfolios', null, 'Gabungan PowerPoint dibuat dari ' . $portfolios->count() . ' portfolio: ' . $portfolios->pluck('name')->implode(', '));
        return $powerPointService->downloadMany($portfolios);
    }

    private function selectedPortfolios(Request $request): Collection
    {
        $ids = $request->validate([
            'portfolio_ids' => ['required', 'array', 'min:1'],
            'portfolio_ids.*' => ['integer', 'distinct', 'exists:portfolios,id'],
        ])['portfolio_ids'];

        $portfolios = Portfolio::whereIn('id', $ids)->get()->keyBy('id');
        $ordered = collect($ids)->map(fn ($id) => $portfolios->get($id))->filter()->values();
        if ($ordered->isEmpty()) {
            abort(422, 'Portfolio yang dipilih tidak ditemukan.');
        }
        return $ordered;
    }

    public function deleteImage(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:thumbnail,banner,gallery'],
            'index' => ['nullable', 'integer', 'min:0'],
        ]);

        $type = $data['type'];

        if ($type === 'gallery') {
            $gallery = array_values($portfolio->gallery ?: []);
            $index = $data['index'] ?? -1;

            if (!array_key_exists($index, $gallery)) {
                return back()->withErrors(['gallery' => 'Gambar galeri tidak ditemukan.']);
            }

            $path = $gallery[$index];
            Storage::disk('public')->delete($path);
            array_splice($gallery, $index, 1);
            $portfolio->gallery = $gallery;
        } else {
            $path = $portfolio->{$type};
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            $portfolio->{$type} = null;
        }

        $portfolio->save();

        ActivityLog::record('delete', 'portfolio_images', $portfolio->id, "Gambar {$type} pada portfolio \"{$portfolio->name}\" dihapus");

        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    public function edit(Portfolio $portfolio): View
    {
        return view('admin.portfolio.form', [
            'portfolio' => $portfolio,
            'websiteTypes' => ServiceRequest::WEBSITE_TYPES,
        ]);
    }

    public function update(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $validated = $this->validated($request, updating: true);
        $validated = $this->handleUploads($request, $validated, $portfolio);

        $portfolio->update($validated);

        ActivityLog::record('update', 'portfolios', $portfolio->id, "Portfolio \"{$portfolio->name}\" diperbarui");

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio diperbarui.');
    }

    public function togglePublish(Portfolio $portfolio): RedirectResponse
    {
        $portfolio->status = $portfolio->status === 'publish' ? 'draft' : 'publish';
        $portfolio->save();

        ActivityLog::record('update', 'portfolios', $portfolio->id, "Portfolio \"{$portfolio->name}\" status diubah ke {$portfolio->status}");

        return back()->with('success', $portfolio->status === 'publish' ? 'Portfolio dipublish — microsite-nya sudah bisa diakses publik.' : 'Portfolio di-unpublish.');
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        foreach (array_filter(array_merge([$portfolio->thumbnail, $portfolio->banner], $portfolio->gallery ?: [])) as $path) {
            Storage::disk('public')->delete($path);
        }

        $portfolio->delete();

        ActivityLog::record('delete', 'portfolios', $portfolio->id, "Portfolio \"{$portfolio->name}\" dan seluruh gambarnya dihapus");

        return back()->with('success', 'Portfolio dan seluruh gambarnya dihapus.');
    }

    private function validated(Request $request, bool $updating = false): array
    {
        $rule = $updating ? 'sometimes' : 'required';

        $data = $request->validate([
            'name' => [$rule, 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'category' => [$rule, 'string', 'max:100'],
            'description' => [$rule, 'string'],
            'problem' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'results' => ['nullable', 'string'],
            'duration' => ['nullable', 'string', 'max:100'],
            'project_year' => ['nullable', 'string', 'max:10'],
            'testimonial_text' => ['nullable', 'string'],
            'testimonial_author' => ['nullable', 'string', 'max:255'],
            'features' => ['nullable', 'string'], // comma separated dari form, dipecah di bawah
            'technologies' => ['nullable', 'string'],
            'url' => ['nullable', 'url', 'max:255'],
            'status' => ['nullable', 'in:draft,publish'],
            'sort_order' => ['nullable', 'integer'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:6144'],
            'gallery.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $data['features'] = isset($data['features'])
            ? array_values(array_filter(array_map('trim', explode(',', $data['features']))))
            : [];
        $data['technologies'] = isset($data['technologies'])
            ? array_values(array_filter(array_map('trim', explode(',', $data['technologies']))))
            : [];

        return $data;
    }

    private function handleUploads(Request $request, array $data, ?Portfolio $portfolio = null): array
    {
        if ($request->hasFile('thumbnail')) {
            if ($portfolio?->thumbnail) {
                Storage::disk('public')->delete($portfolio->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('portfolios/thumbnails', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($portfolio?->banner) {
                Storage::disk('public')->delete($portfolio->banner);
            }
            $data['banner'] = $request->file('banner')->store('portfolios/banners', 'public');
        }

        if ($request->hasFile('gallery')) {
            $existing = $portfolio?->gallery ?? [];
            $uploaded = array_map(
                fn ($file) => $file->store('portfolios/gallery', 'public'),
                $request->file('gallery')
            );
            $data['gallery'] = array_merge($existing, $uploaded);
        }

        return $data;
    }
}
