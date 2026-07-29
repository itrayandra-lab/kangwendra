<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Posts;
use App\Models\RefArticle;
use App\Models\EditorPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalNews = Posts::count();
        $newsThisYear = Posts::whereYear('created_at', date('Y'))->count();
        $newsToday = Posts::whereDate('created_at', date('Y-m-d'))->count();

        // AI Pipeline Stats (Ref Articles breakdown)
        // ai_research_status values: idle, researching, done, failed
        $aiStats = [
            'total_ref_articles' => RefArticle::count(),
            'pending'            => RefArticle::where('ai_research_status', 'idle')->count(),
            'processing'         => RefArticle::where('ai_research_status', 'researching')->count(),
            'published'          => RefArticle::where('ai_research_status', 'done')->count(),
            'failed'             => RefArticle::where('ai_research_status', 'failed')->count(),
        ];

        // Published today (all active posts)
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $publishedToday = Posts::whereBetween('published_at', [$todayStart, $todayEnd])
            ->where('status', 'active')
            ->count();

        // AI-generated posts today
        $aiPostsToday = Posts::where('published_by', 'system')
            ->whereDate('created_at', date('Y-m-d'))
            ->count();

        // Editor/Keyword stats
        $researchStats = [
            'total_keywords'  => EditorPreference::count(),
            'avg_confidence'  => round(EditorPreference::avg('confidence') ?? 0, 1),
            // Ready to generate = idle RefArticles
            'pending_recs'    => RefArticle::where('ai_research_status', 'idle')->count(),
            // Total RefArticles that have been processed (researching + done + failed)
            'processed'       => RefArticle::whereIn('ai_research_status', ['researching', 'done', 'failed'])->count(),
        ];

        return view('pages.admin.home.index', [
            'totalUsers'     => $totalUsers,
            'totalNews'      => $totalNews,
            'newsThisYear'   => $newsThisYear,
            'newsToday'      => $newsToday,
            'page'           => 'Dashboard',
            'aiStats'        => $aiStats,
            'publishedToday' => $publishedToday,
            'aiPostsToday'   => $aiPostsToday,
            'researchStats'  => $researchStats,
        ]);
    }
}
