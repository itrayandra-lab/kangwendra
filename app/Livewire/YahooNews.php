<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Posts;
use Livewire\WithPagination;

class YahooNews extends Component
{
    use WithPagination;

    public function render()
    {
        // Tampilkan posts yang di-generate dari Yahoo Tech scraper
        // Ini adalah artikel AI yang sudah diproses dari tech.yahoo.com
        $posts = Posts::where('source', 'like', '%tech.yahoo.com%')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('livewire.yahoo-news', compact('posts'));
    }
}
