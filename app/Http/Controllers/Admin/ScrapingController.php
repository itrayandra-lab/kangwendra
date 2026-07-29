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

        // Stats: only count items that have NOT been moved yet
        // (research_recommendations items are auto-deleted after approve,
        //  so any remaining items with ref_article_id are orphaned leftovers)
        $stats = [
            // Pending = awaiting approve/reject in hasil-scraping (no ref_article_id)
            'pending'   => ResearchRecommendation::whereNull('ref_article_id')
                           ->where('status', 'pending')->count(),
            // All research_recommendations that exist (should be 0 for pending-only workflow)
            'total'     => ResearchRecommendation::count(),
            // Moved = has ref_article_id (already approved — orphaned/pre-migration leftovers)
            'moved'     => ResearchRecommendation::whereNotNull('ref_article_id')->count(),
            // Total in ref_articles (the actual pipeline after approve)
            'ref_articles_total' => RefArticle::count(),
        ];

        return view('pages.admin.scraping.index', compact('page', 'keywords', 'stats'));
    }

    /**
     * Research 1 keyword - SYNCHRONOUS (results appear immediately).
     */
    public function research(Request $request)
    {
        set_time_limit(0); // No PHP timeout - sitemap scraping can take time

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
            $allKeywords = ScraperConfig::getKeywords();
            $inConfig = in_array($keyword, $allKeywords);

            $msg = "Research selesai! 0 URL ditemukan untuk '{$keyword}'.";
            if ($inConfig) {
                $msg .= " Keyword ini ada di ScraperConfig tapi tidak ditemukan di sitemap SEJ/SEL. ";
                $msg .= "Coba lagi nanti atau ganti dengan keyword lain.";
            } else {
                $msg .= " Keyword ini BELUM ada di ScraperConfig. ";
                $msg .= "Pergi ke Scraper Config di menu Scraping untuk menambahnya.";
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

    // ── KEYWORD MANAGEMENT (moved from RefArticleController) ────────────

    /**
     * List all keywords from editor_preferences
     */
    public function indexKeywords()
    {
        $page = 'Kelola Kata Kunci';
        $keywords = EditorPreference::orderBy('created_at', 'desc')->get();
        return view('pages.admin.scraping.keywords', compact('page', 'keywords'));
    }

    /**
     * Add a new keyword
     */
    public function storeKeyword(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|min:2|max:50|unique:editor_preferences,keyword',
        ]);

        EditorPreference::create([
            'keyword' => trim(strtolower($validated['keyword'])),
        ]);

        return back()->with('success', "Kata kunci '{$validated['keyword']}' berhasil ditambahkan.");
    }

    /**
     * Delete a keyword
     */
    public function destroyKeyword($id)
    {
        $pref = EditorPreference::findOrFail($id);
        $keyword = $pref->keyword;
        $pref->delete();

        return back()->with('success', "Kata kunci '{$keyword}' berhasil dihapus.");
    }

    /**
     * Clear all blocklists from all EditorPreference records
     */
    public function clearBlocklists()
    {
        $cleared = EditorPreference::whereNotNull('blocklist_urls')
            ->where('blocklist_urls', '!=', '')
            ->where('blocklist_urls', '!=', '[]')
            ->update(['blocklist_urls' => null, 'blocklist_patterns' => null]);

        return back()->with('info', "Blocklist berhasil dibersihkan dari {$cleared} record.");
    }
}
