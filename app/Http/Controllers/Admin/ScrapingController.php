<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EditorPreference;
use App\Models\RefArticle;
use App\Models\ResearchRecommendation;
use App\Models\ScraperConfig;
use App\Jobs\KeywordResearchJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScrapingController extends Controller
{
    /**
     * Halaman Scraping - Form research ONLY, no table.
     */
    public function index()
    {
        $page = 'Scraping';
        $keywords = ScraperConfig::getKeywords();
        $stats = [
            'total'    => ResearchRecommendation::count(),
            'pending'  => ResearchRecommendation::pending()->count(),
            'moved'    => ResearchRecommendation::whereNotNull('ref_article_id')->count(),
        ];

        return view('pages.admin.scraping.index', compact('page', 'keywords', 'stats'));
    }

    /**
     * Research 1 keyword - SYNCHRONOUS (results appear immediately).
     */
    public function research(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|min:2|max:255',
        ]);

        $keyword = strtolower(trim($validated['keyword']));

        // Delete old low-score recommendations
        ResearchRecommendation::byKeyword($keyword)
            ->where('confidence_score', '<', 45)
            ->delete();

        // Reset rejected
        ResearchRecommendation::byKeyword($keyword)
            ->where('status', 'rejected')
            ->update(['status' => 'pending']);

        // Run research synchronously
        $job = new KeywordResearchJob($keyword);
        $job->handle(app(\App\Services\SitemapScraperService::class));

        $count = ResearchRecommendation::byKeyword($keyword)->count();

        if ($count === 0) {
            // Check if keyword exists in config at all
            $allKeywords = ScraperConfig::getKeywords();
            $inConfig = in_array($keyword, $allKeywords);

            $msg = "Research selesai! 0 URL ditemukan untuk '{$keyword}'.";
            if ($inConfig) {
                $msg .= " Keyword ini ada di ScraperConfig tapi tidak ditemukan di sitemap SEJ/SEL. ";
                $msg .= "Coba lagi nanti atau ganti dengan keyword lain.";
            } else {
                $msg .= " Keyword ini BELUM ada di ScraperConfig. ";
                $msg .= "Pergi ke Ref Articles > Kelola Kata Kunci untuk menambahnya.";
            }

            return redirect()
                ->route('admin.hasil-scraping.index', ['keyword' => $keyword])
                ->with('warning', $msg);
        }

        return redirect()
            ->route('admin.hasil-scraping.index', ['keyword' => $keyword])
            ->with('success', "Research selesai! {$count} URL ditemukan untuk '{$keyword}'.");
    }

    /**
     * Research ALL keywords - ASYNC via queue (dispatch jobs, don't wait).
     */
    public function researchAll(Request $request)
    {
        $keywords = ScraperConfig::getKeywords();
        $dispatched = 0;

        foreach ($keywords as $keyword) {
            $kw = strtolower(trim($keyword));

            // Delete old low-score recommendations
            ResearchRecommendation::byKeyword($kw)
                ->where('confidence_score', '<', 45)
                ->delete();

            // Reset rejected
            ResearchRecommendation::byKeyword($kw)
                ->where('status', 'rejected')
                ->update(['status' => 'pending']);

            // Dispatch async job (NOT handle() synchronous)
            KeywordResearchJob::dispatch($kw);
            $dispatched++;
        }

        return redirect()
            ->route('admin.hasil-scraping.index')
            ->with('success', "{$dispatched} keyword di-queue untuk scraping. Hasil akan muncul di halaman Hasil Scraping.");
    }
}
