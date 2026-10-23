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
        $query = Portfolio::published()->orderByDesc('sort_order')->orderByDesc('created_at');

        $category = $request->query('category');
        if ($category) {
            $query->where('category', $category);
        }

        return view('portfolio.index', [
            'portfolios' => $query->paginate(9)->withQueryString(),
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

        return view('portfolio.show', compact('portfolio', 'related'));
    }
}
