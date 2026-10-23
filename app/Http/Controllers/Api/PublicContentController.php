<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicContentController extends Controller
{
    // GET /api/portfolios?category=E-Commerce
    public function portfolios(Request $request): JsonResponse
    {
        $query = Portfolio::published()->orderByDesc('sort_order')->orderByDesc('created_at');

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        return response()->json($query->paginate(12));
    }

    // GET /api/portfolios/{slug}
    public function portfolio(string $slug): JsonResponse
    {
        $portfolio = Portfolio::published()->where('slug', $slug)->firstOrFail();

        return response()->json($portfolio);
    }

    // GET /api/company-profile
    public function companyProfile(): JsonResponse
    {
        return response()->json(CompanyProfile::current());
    }
}
