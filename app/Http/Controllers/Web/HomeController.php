<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\TeamMember;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'portfolios' => Portfolio::published()->orderByDesc('sort_order')->orderByDesc('created_at')->limit(3)->get(),
            'teamMembers' => TeamMember::active()->orderBy('sort_order')->orderBy('name')->limit(6)->get(),
        ]);
    }
}
