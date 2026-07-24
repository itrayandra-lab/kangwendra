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
        return back()->with('error', 'PERINGATAN: RSS fetch DINONAKTIFKAN permanen. Command ini save langsung ke Posts tanpa AI paraphrase (copyright risk). Hubungi developer jika perlu di-enable kembali.');
    }
}
