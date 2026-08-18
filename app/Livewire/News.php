<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Posts;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class News extends Component
{
    use WithPagination;

    public function render()
    {
        // Ambil postingan dengan kategori Teknologi
        $posts = Posts::whereHas('category', function($q) {
            $q->where('name', 'Teknologi');
        })
        ->where('status', 'active')
        ->whereNotNull('published_at')
        ->where('published_at', '<=', Carbon::now())
        ->orderBy('published_at', 'desc')
        ->paginate(10);

        return view('livewire.news', compact('posts'));
    }
}
