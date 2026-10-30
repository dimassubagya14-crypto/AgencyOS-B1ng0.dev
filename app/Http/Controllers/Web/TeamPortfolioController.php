<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\View\View;

class TeamPortfolioController extends Controller
{
    public function show(string $slug): View
    {
        $member = TeamMember::where('slug', $slug)
            ->where('portfolio_published', true)
            ->where('is_active', true)
            ->firstOrFail();

        return view('team.portfolio', ['member' => $member, 'preview' => false]);
    }
}
