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
        // Ambil postingan yang punya tag "Yahoo AI" atau dari sumber Yahoo
        $posts = Posts::where('tags', 'like', '%Yahoo AI%')
            ->orWhere('title', 'like', '%AI%')
            ->orWhere('title', 'like', '%artificial intelligence%')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('livewire.yahoo-news', compact('posts'));
    }
}
