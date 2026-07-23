<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NewsService;

class RssController extends Controller
{
    public function yahooIndex()
    {
        $page = 'RSS Tech & AI Feeds';
        return view('pages.admin.rss.yahoo-index', compact('page'));
    }

    public function fetchYahoo(Request $request)
    {
        try {
            $newsService = new NewsService();
            $date = $request->input('date');

            $newsItems = $newsService->fetchFromYahooAiRss($date);
            $count = $newsService->saveNewsToDatabase($newsItems);

            return back()->with('success', "RSS berhasil diambil! {$count} artikel disimpan dari berbagai sumber.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil RSS: ' . $e->getMessage());
        }
    }
}
