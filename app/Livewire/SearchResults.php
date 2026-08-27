<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Question;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class SearchResults extends Component
{
    #[Url]
    public string $q = '';

    public function render(): View
    {
        $posts = collect();
        $users = collect();
        $questions = collect();

        if (strlen($this->q) >= 2) {
            $term = '%'.$this->q.'%';

            // Performance: Eager load relationships and use withCount/withExists to avoid N+1 queries
            // when rendering the search results cards.
            $posts = Post::where('title', 'ilike', $term)
                ->orWhere('content', 'ilike', $term)
                ->with(['user.daKhongCuc', 'images'])
                ->withCount(['likes', 'allComments'])
                ->withExists(['likes' => fn ($q) => $q->where('user_id', auth()->id())])
                ->withExists(['bookmarks' => fn ($q) => $q->where('user_id', auth()->id())])
                ->latest()
                ->take(20)
                ->get();

            $users = User::where('name', 'ilike', $term)
                ->orWhere('username', 'ilike', $term)
                ->take(10)
                ->get();

            $questions = Question::where('title', 'ilike', $term)
                ->orWhere('body', 'ilike', $term)
                ->with('user')
                ->withCount('answers')
                ->latest()
                ->take(10)
                ->get();
        }

        return view('livewire.search-results', [
            'posts' => $posts,
            'users' => $users,
            'questions' => $questions,
        ])->layout('layouts.app', ['title' => 'Tìm kiếm: '.$this->q.' — DSCons']);
    }
}
