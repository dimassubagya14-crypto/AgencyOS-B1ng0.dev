<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\View\View;

class TeamPortfolioController extends Controller
{
    public function show(string $slug): View
    {
        return view('team.portfolio', ['member' => $this->published($slug), 'preview' => false]);
    }

    // Halaman CV siap cetak (Simpan sebagai PDF lewat dialog cetak browser).
    public function cv(string $slug): View
    {
        return view('team.cv', ['member' => $this->published($slug), 'preview' => false]);
    }

    private function published(string $slug): TeamMember
    {
        return TeamMember::where('slug', $slug)
            ->where('portfolio_published', true)
            ->where('is_active', true)
            ->firstOrFail();
    }
}
