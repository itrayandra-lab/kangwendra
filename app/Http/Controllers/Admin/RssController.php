<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NewsService;

class RssController extends Controller
{
    public function yahooIndex()
    {
        $page = 'RSS Yahoo AI';
        return view('pages.admin.rss.yahoo-index', compact('page'));
    }

    public function fetchYahoo(Request $request)
    {
        try {
            $newsService = new NewsService();
            $date = $request->input('date');
            
            $newsItems = $newsService->fetchFromYahooAiRss($date);
            $count = $newsService->saveNewsToDatabase($newsItems);
            
            return back()->with('success', "RSS Yahoo AI berhasil diambil! Berhasil menyimpan {$count} artikel.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil RSS Yahoo: ' . $e->getMessage());
        }
    }
}
