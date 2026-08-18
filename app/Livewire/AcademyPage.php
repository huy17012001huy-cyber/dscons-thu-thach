<?php

namespace App\Livewire;

use App\Models\Course;
use Livewire\Attributes\Url;
use Livewire\Component;

class AcademyPage extends Component
{
    #[Url]
    public string $pillar = '';

    #[Url]
    public string $difficulty = '';

    public function setPillar(string $p): void
    {
        $this->pillar = $this->pillar === $p ? '' : $p;
    }

    public function render()
    {
        $query = Course::where('is_published', true);

        if ($this->pillar) {
            $query->where('pillar', $this->pillar);
        }

        if ($this->difficulty) {
            $query->where('difficulty', $this->difficulty);
        }

        $courses = $query->withCount(['enrollments', 'modules'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.academy-page', ['courses' => $courses])
            ->layout('layouts.app', ['title' => 'Khóa học — The All In Plan™']);
    }
}
