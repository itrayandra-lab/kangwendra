<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiArticleJob;
use App\Jobs\ScrapeArticleJob;
use App\Models\RefArticle;
use App\Services\TechPharmaScraperService;
use App\Services\YahooTechScraperService;
use Illuminate\Http\Request;

class RefArticleController extends Controller
{
    public function index(Request $request)
    {
        $page   = 'Artikel Referensi';
        $status = $request->input('status');
        $source = $request->input('source');

        $query = RefArticle::latest();
        if ($status) $query->where('ai_status', $status);
        if ($source) $query->where('source_domain', 'like', "%{$source}%");

        $articles = $query->paginate(20)->withQueryString();

        $stats = [
            'total'      => RefArticle::count(),
            'pending'    => RefArticle::where('ai_status', 'pending')->count(),
            'processing' => RefArticle::where('ai_status', 'processing')->count(),
            'done'       => RefArticle::where('ai_status', 'done')->count(),
            'failed'     => RefArticle::where('ai_status', 'failed')->count(),
        ];

        return view('pages.admin.ref-articles.index', compact('page', 'articles', 'stats', 'status', 'source'));
    }

    public function scrape()
    {
        set_time_limit(0);

        try {
            // Batasi scraping max 5 article per klik
            $scraper = new YahooTechScraperService(5);
            $saved = $scraper->scrapeAndSave();
            return back()->with('success', "Scraping selesai! {$saved} artikel dari Yahoo Tech berhasil disimpan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal scraping Yahoo Tech: ' . $e->getMessage());
        }
    }

    public function scrapePharma()
    {
        set_time_limit(0);

        try {
            // Batasi scraping max 3 article per klik
            $scraper = new TechPharmaScraperService(3);
            $saved = $scraper->scrapeAndSave();
            return back()->with('success', "Scraping selesai! {$saved} artikel Tech Pharma berhasil disimpan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal scraping Tech Pharma: ' . $e->getMessage());
        }
    }

    public function scrapeAll()
    {
        set_time_limit(0);

        try {
            // Batasi: 5 Yahoo Tech + 3 Pharma
            $yahooSaved  = (new YahooTechScraperService(5))->scrapeAndSave();
            $pharmaSaved = (new TechPharmaScraperService(3))->scrapeAndSave();
            $total = $yahooSaved + $pharmaSaved;
            return back()->with('success', "Scraping selesai! {$yahooSaved} Yahoo Tech + {$pharmaSaved} Tech Pharma = {$total} total (maks 5+3).");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal scraping: ' . $e->getMessage());
        }
    }

    public function generateOne(RefArticle $refArticle)
    {
        set_time_limit(0);

        if ($refArticle->ai_status === 'done') {
            return back()->with('error', 'Artikel ini sudah pernah di-generate.');
        }

        $refArticle->update(['ai_status' => 'pending', 'ai_error' => null]);

        try {
            $job = new GenerateAiArticleJob($refArticle->id);
            $job->handle();
            return back()->with('success', "Generate AI selesai untuk \"{$refArticle->title}\".");
        } catch (\Exception $e) {
            $refArticle->update(['ai_status' => 'failed', 'ai_error' => $e->getMessage()]);
            return back()->with('error', 'Gagal generate AI: ' . $e->getMessage());
        }
    }

    public function generateAll(Request $request)
    {
        // Bulk generate: dispatch ke queue
        $limit = (int) $request->input('limit', 20);

        RefArticle::failed()->update(['ai_status' => 'pending', 'ai_error' => null]);
        $pending = RefArticle::pending()->latest()->take($limit)->get();

        if ($pending->isEmpty()) {
            return back()->with('error', 'Tidak ada artikel yang perlu di-generate.');
        }

        $dispatched = 0;
        foreach ($pending as $ref) {
            GenerateAiArticleJob::dispatch($ref->id);
            $dispatched++;
        }

        return back()->with('success', "{$dispatched} job generate AI dikirim ke queue. Jalankan di terminal: php artisan queue:work --stop-when-empty");
    }

    public function retry(RefArticle $refArticle)
    {
        set_time_limit(0);

        $refArticle->update(['ai_status' => 'pending', 'ai_error' => null]);

        try {
            $job = new GenerateAiArticleJob($refArticle->id);
            $job->handle();
            return back()->with('success', "Retry selesai untuk \"{$refArticle->title}\".");
        } catch (\Exception $e) {
            $refArticle->update(['ai_status' => 'failed', 'ai_error' => $e->getMessage()]);
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroy(RefArticle $refArticle)
    {
        $refArticle->delete();
        return back()->with('success', 'Artikel referensi berhasil dihapus.');
    }

    public function show(RefArticle $refArticle)
    {
        $page = 'Detail Artikel Referensi';
        return view('pages.admin.ref-articles.show', compact('page', 'refArticle'));
    }
}
