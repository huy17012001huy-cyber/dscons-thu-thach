<?php

namespace App\Livewire;

use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use App\Models\RecruiterEntitlement;
use App\Models\RecruiterProfile;
use App\Models\RecruitmentContactRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Recruitment\Application\RecruiterContactService;
use Modules\Recruitment\Contracts\CandidateMatcher;
use Modules\Recruitment\Contracts\JobDescriptionParser;

class RecruiterDashboard extends Component
{
    public bool $isAdminPreview = false;

    public ?int $recruiterUserId = null;

    public string $jobDescription = '';

    public string $discipline = '';

    public string $skill = '';

    public string $workMode = '';

    public string $availability = '';

    public int $minYears = 0;

    public ?int $selectedCandidate = null;

    public string $contactMessage = '';

    public string $activeTab = 'candidates';

    public function mount(?RecruiterProfile $recruiter = null): void
    {
        $this->isAdminPreview = request()->routeIs('community.manage.recruitment.preview.recruiter');
        $user = auth()->user();

        if ($this->isAdminPreview) {
            abort_unless($user instanceof User && $user->isCommunityAdmin(brand()->id), 403);
            abort_unless($recruiter && $recruiter->brand_id === brand()->id && $recruiter->isVerified(), 404);
            $this->recruiterUserId = (int) $recruiter->user_id;

            return;
        }

        abort_unless($user instanceof User && $user->isRecruiter(), 403);
        $this->recruiterUserId = (int) $user->id;
    }

    public function search(): void
    {
        $this->validate([
            'jobDescription' => 'nullable|string|max:10000',
            'discipline' => 'nullable|string|max:120',
            'skill' => 'nullable|string|max:120',
            'workMode' => 'nullable|string|max:40',
            'availability' => 'nullable|string|max:40',
            'minYears' => 'integer|min:0|max:60',
        ]);
        $this->activeTab = 'candidates';
    }

    public function selectCandidate(int $engineerId): void
    {
        $this->selectedCandidate = $engineerId;
    }

    public function clearCandidate(): void
    {
        $this->selectedCandidate = null;
    }

    public function requestContact(int $engineerId): void
    {
        abort_if($this->isAdminPreview, 403);
        $recruiter = auth()->user();
        abort_unless($recruiter instanceof User && $recruiter->isRecruiter(), 403);

        $profile = EngineerProfile::with(['cv' => fn ($query) => $query->where('status', 'published')])
            ->where('brand_id', brand()->id)
            ->where('user_id', $engineerId)
            ->where('is_searchable', true)
            ->firstOrFail();
        $cv = $profile->cv;
        abort_unless($cv instanceof EngineerCv, 422);

        try {
            app(RecruiterContactService::class)->request($recruiter, $profile, $cv, $this->contactMessage ?: null);
            $this->contactMessage = '';
            $this->dispatch('toast', message: 'ÄÃ£ gá»­i yÃªu cáº§u. Chá» ká»¹ sÆ° cháº¥p thuáº­n Ä‘á»ƒ má»Ÿ liÃªn há»‡.', type: 'success');
        } catch (\Throwable $exception) {
            $this->addError('contact', $exception->getMessage());
        }
    }

    public function render(): View
    {
        $criteria = app(JobDescriptionParser::class)->parse($this->jobDescription);
        if ($this->discipline !== '') {
            $criteria['discipline'] = $this->discipline;
        }
        if ($this->minYears > 0) {
            $criteria['years'] = $this->minYears;
        }
        if ($this->skill !== '') {
            $skills = preg_split('/[,\n]+/', $this->skill) ?: [];
            $criteria['skills'] = array_values(array_filter(array_map('trim', $skills)));
        }

        $candidates = EngineerProfile::query()
            ->with(['cv' => fn ($query) => $query->where('status', 'published')])
            ->where('brand_id', brand()->id)
            ->where('is_searchable', true)
            ->whereHas('cv', fn ($query) => $query->where('status', 'published'))
            ->when($criteria['discipline'] ?? null, fn ($query, $value) => $query->whereRaw('LOWER(discipline) LIKE ?', ['%'.strtolower($value).'%']))
            ->when($criteria['years'] > 0, fn ($query, $value) => $query->where('years_experience', '>=', $value))
            ->when($this->workMode !== '', fn ($query) => $query->where('work_mode', $this->workMode))
            ->when($this->availability !== '', fn ($query) => $query->where('availability', $this->availability))
            ->latest('updated_at')
            ->limit(60)
            ->get()
            ->filter(fn (EngineerProfile $profile): bool => $profile->cv instanceof EngineerCv)
            ->map(function (EngineerProfile $profile) use ($criteria): array {
                $cv = $profile->cv;
                assert($cv instanceof EngineerCv);
                $match = app(CandidateMatcher::class)->score($criteria, $cv);
                $skills = collect($cv->skills())->map(fn ($skill) => is_array($skill) ? ($skill['name'] ?? '') : $skill)->filter()->take(8)->values()->all();

                return [
                    'id' => $profile->user_id,
                    'code' => $profile->anonymized_code,
                    'headline' => $profile->headline ?: 'Ká»¹ sÆ° BIM/MEP',
                    'discipline' => $profile->discipline,
                    'years' => $profile->years_experience,
                    'location' => $profile->location,
                    'work_mode' => $profile->work_mode,
                    'availability' => $profile->availability,
                    'summary' => $profile->summary,
                    'skills' => $skills,
                    'score' => $match['score'],
                    'reasons' => $match['reasons'],
                    'experiences' => collect($cv->experiences())->take(6)->values()->all(),
                    'projects' => data_get($cv->data, 'projects', []),
                    'certifications' => data_get($cv->data, 'certifications', []),
                ];
            })
            ->filter(function (array $candidate): bool {
                if ($this->skill === '') {
                    return true;
                }
                $needle = strtolower($this->skill);

                return collect($candidate['skills'])->contains(fn ($skill) => str_contains(strtolower((string) $skill), $needle));
            })
            ->sortByDesc('score')
            ->values();

        $connections = RecruitmentContactRequest::query()
            ->where('brand_id', brand()->id)
            ->where('recruiter_id', $this->currentRecruiterUserId())
            ->with(['engineer.engineerProfile', 'conversation'])
            ->latest()
            ->get();

        $creditSummary = RecruiterEntitlement::query()
            ->where('brand_id', brand()->id)
            ->where('recruiter_id', $this->currentRecruiterUserId())
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get()
            ->reduce(fn (array $summary, $item) => [
                'available' => $summary['available'] + $item->availableCredits(),
                'reserved' => $summary['reserved'] + (int) $item->credits_reserved,
                'used' => $summary['used'] + (int) $item->credits_used,
            ], ['available' => 0, 'reserved' => 0, 'used' => 0]);

        $selected = $this->selectedCandidate ? $candidates->firstWhere('id', $this->selectedCandidate) : null;

        return view('livewire.recruiter-dashboard', compact('candidates', 'criteria', 'connections', 'creditSummary', 'selected'))
            ->layout('layouts.recruiter', ['title' => 'TÃ¬m á»©ng viÃªn BIM/MEP']);
    }

    private function currentRecruiterUserId(): int
    {
        abort_unless($this->recruiterUserId !== null, 403);

        return $this->recruiterUserId;
    }
}
