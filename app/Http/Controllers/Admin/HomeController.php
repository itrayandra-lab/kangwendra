<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Posts;
use App\Models\RefArticle;
use App\Models\EditorPreference;
use App\Models\ResearchRecommendation;
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

        // AI Pipeline Stats
        $aiStats = [
            'total_ref_articles'  => RefArticle::count(),
            'pending'              => RefArticle::where('ai_status', 'pending')->count(),
            'processing'          => RefArticle::where('ai_status', 'processing')->count(),
            'published'            => RefArticle::where('ai_status', 'done')->count(),
            'failed'              => RefArticle::where('ai_status', 'failed')->count(),
        ];

        // Published today (scheduled)
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $publishedToday = Posts::whereBetween('published_at', [$todayStart, $todayEnd])
            ->where('status', 'active')
            ->count();

        // AI-generated posts
        $aiPostsToday = Posts::where('published_by', 'system')
            ->whereDate('created_at', date('Y-m-d'))
            ->count();

        // Research stats
        $researchStats = [
            'total_recommendations' => ResearchRecommendation::count(),
            'pending_recs'          => ResearchRecommendation::pending()->count(),
            'scraped_recs'         => ResearchRecommendation::scraped()->count(),
            'total_keywords'        => EditorPreference::count(),
            'avg_confidence'        => round(EditorPreference::avg('confidence') ?? 0, 1),
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
