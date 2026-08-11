<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Posts;
use App\Models\RefArticle;
use App\Models\EditorPreference;
use App\Models\ScraperConfig;
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

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        // Single aggregated query for all post stats
        $postsStats = Posts::selectRaw('
            COUNT(*) AS total,
            SUM(YEAR(created_at) = ?) AS news_this_year,
            SUM(created_at BETWEEN ? AND ?) AS news_today,
            SUM(published_at BETWEEN ? AND ? AND status = "active") AS published_today,
            SUM(published_by = "system" AND created_at BETWEEN ? AND ?) AS ai_posts_today
        ', [date('Y'), $todayStart, $todayEnd, $todayStart, $todayEnd, $todayStart, $todayEnd])->first();

        // Single aggregated query for all RefArticle stats
        $refStats = RefArticle::selectRaw('
            COUNT(*) AS total,
            SUM(ai_research_status = "idle") AS pending,
            SUM(ai_research_status = "researching") AS processing,
            SUM(ai_research_status = "done") AS published,
            SUM(ai_research_status = "failed") AS failed,
            SUM(ai_research_status IN ("researching", "done", "failed")) AS processed
        ')->first();

        // Single aggregated query for keyword stats
        $keywordStats = EditorPreference::selectRaw('COUNT(*) AS total, AVG(confidence) AS avg_confidence')->first();

        $aiStats = [
            'total_ref_articles' => (int) $refStats->total,
            'pending'            => (int) $refStats->pending,
            'processing'         => (int) $refStats->processing,
            'published'          => (int) $refStats->published,
            'failed'             => (int) $refStats->failed,
        ];

        $researchStats = [
            'total_keywords'  => (int) $keywordStats->total,
            'avg_confidence'  => round((float) $keywordStats->avg_confidence, 1),
            'pending_recs'    => (int) $refStats->pending,
            'processed'       => (int) $refStats->processed,
        ];

        return view('pages.admin.home.index', [
            'totalUsers'     => $totalUsers,
            'totalNews'      => (int) $postsStats->total,
            'newsThisYear'   => (int) $postsStats->news_this_year,
            'newsToday'      => (int) $postsStats->news_today,
            'page'           => 'Dashboard',
            'aiStats'        => $aiStats,
            'publishedToday' => (int) $postsStats->published_today,
            'aiPostsToday'   => (int) $postsStats->ai_posts_today,
            'researchStats'  => $researchStats,
            'publishHours'   => ScraperConfig::getPublishScheduleHours(),
        ]);
    }
}
