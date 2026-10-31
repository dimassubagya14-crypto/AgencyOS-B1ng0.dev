<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    // GET /portfolio
    public function index(Request $request): View
    {
        // Semua project dimuat sekaligus supaya cari, filter, dan urutan di halaman berjalan
        // instan tanpa muat ulang (jumlah project agency relatif kecil).
        $portfolios = Portfolio::published()
            ->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->limit(120)
            ->get();

        $category = $request->query('category');

        return view('portfolio.index', [
            'portfolios' => $portfolios,
            'categories' => Portfolio::published()->distinct()->pluck('category'),
            'category' => $category,
        ]);
    }

    // GET /portfolio/{slug} — halaman "microsite" per project, tapi tetap
    // dikelola dari admin CRUD portfolio yang sama (bukan situs terpisah).
    public function show(string $slug): View
    {
        $portfolio = Portfolio::published()->where('slug', $slug)->firstOrFail();

        $related = Portfolio::published()
            ->where('id', '!=', $portfolio->id)
            ->where('category', $portfolio->category)
            ->limit(3)
            ->get();

        // Navigasi ke project sebelumnya / berikutnya (urutan sama dengan halaman daftar).
        $ordered = Portfolio::published()
            ->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->get(['id', 'slug', 'name', 'category']);
        $position = $ordered->search(fn ($item) => $item->id === $portfolio->id);
        $prev = ($position !== false && $position > 0) ? $ordered[$position - 1] : null;
        $next = ($position !== false && $position < $ordered->count() - 1) ? $ordered[$position + 1] : null;

        return view('portfolio.show', compact('portfolio', 'related', 'prev', 'next'));
    }
}
