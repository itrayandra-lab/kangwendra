<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiArticleJob;
use App\Models\RefArticle;
use App\Services\YahooTechScraperService;
use Illuminate\Http\Request;

class RefArticleController extends Controller
{
    /**
     * Daftar semua artikel referensi.
     */
    public function index(Request $request)
    {
        $page   = 'Artikel Referensi';
        $status = $request->input('status');

        $query = RefArticle::latest();
        if ($status) {
            $query->where('ai_status', $status);
        }

        $articles = $query->paginate(20)->withQueryString();

        $stats = [
            'total'      => RefArticle::count(),
            'pending'    => RefArticle::where('ai_status', 'pending')->count(),
            'processing' => RefArticle::where('ai_status', 'processing')->count(),
            'done'       => RefArticle::where('ai_status', 'done')->count(),
            'failed'     => RefArticle::where('ai_status', 'failed')->count(),
        ];

        return view('pages.admin.ref-articles.index', compact('page', 'articles', 'stats', 'status'));
    }

    /**
     * Scrape artikel baru dari tech.yahoo.com.
     */
    public function scrape()
    {
        try {
            $scraper = new YahooTechScraperService();
            $saved   = $scraper->scrapeAndSave();

            return back()->with('success', "Scraping selesai! {$saved} artikel referensi baru berhasil disimpan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal scraping: ' . $e->getMessage());
        }
    }

    /**
     * Generate artikel AI untuk satu artikel referensi.
     */
    public function generateOne(RefArticle $refArticle)
    {
        if ($refArticle->ai_status === 'done') {
            return back()->with('error', 'Artikel ini sudah pernah di-generate.');
        }

        $refArticle->update(['ai_status' => 'pending', 'ai_error' => null]);
        GenerateAiArticleJob::dispatch($refArticle->id);

        return back()->with('success', "Job generate AI untuk artikel \"{$refArticle->title}\" berhasil dikirim ke queue.");
    }

    /**
     * Generate artikel AI untuk semua yang masih pending / failed.
     */
    public function generateAll()
    {
        // Reset failed ke pending agar bisa dicoba ulang
        RefArticle::failed()->update(['ai_status' => 'pending', 'ai_error' => null]);

        $pending = RefArticle::pending()->get();

        if ($pending->isEmpty()) {
            return back()->with('error', 'Tidak ada artikel yang perlu di-generate.');
        }

        foreach ($pending as $ref) {
            GenerateAiArticleJob::dispatch($ref->id);
        }

        return back()->with('success', "{$pending->count()} job generate AI berhasil dikirim ke queue.");
    }

    /**
     * Retry generate untuk artikel yang failed.
     */
    public function retry(RefArticle $refArticle)
    {
        $refArticle->update(['ai_status' => 'pending', 'ai_error' => null]);
        GenerateAiArticleJob::dispatch($refArticle->id);

        return back()->with('success', "Retry generate AI untuk \"{$refArticle->title}\" berhasil dikirim.");
    }

    /**
     * Hapus artikel referensi.
     */
    public function destroy(RefArticle $refArticle)
    {
        $refArticle->delete();
        return back()->with('success', 'Artikel referensi berhasil dihapus.');
    }

    /**
     * Lihat detail artikel referensi.
     */
    public function show(RefArticle $refArticle)
    {
        $page = 'Detail Artikel Referensi';
        return view('pages.admin.ref-articles.show', compact('page', 'refArticle'));
    }
}
