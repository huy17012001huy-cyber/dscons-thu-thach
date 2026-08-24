<?php

namespace App\Livewire;

use App\Models\EngineerProfile;
use App\Models\RecruiterEntitlement;
use App\Models\RecruitmentContactRequest;
use App\Services\CandidateMatcher;
use App\Services\JobDescriptionParser;
use App\Services\RecruiterContactService;
use Livewire\Component;

class RecruiterDashboard extends Component
{
    public string $jobDescription = '';
    public string $discipline = '';
    public string $skill = '';
    public string $workMode = '';
    public string $availability = '';
    public int $minYears = 0;
    public ?int $selectedCandidate = null;
    public string $contactMessage = '';
    public string $activeTab = 'candidates';

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
        $profile = EngineerProfile::with(['cv' => fn ($query) => $query->where('status', 'published')])
            ->where('user_id', $engineerId)
            ->where('is_searchable', true)
            ->firstOrFail();
        abort_unless($profile->cv, 422, 'Ứng viên chưa công khai CV hoàn chỉnh.');

        try {
            app(RecruiterContactService::class)->request(auth()->user(), $profile, $profile->cv, $this->contactMessage ?: null);
            $this->contactMessage = '';
            $this->dispatch('toast', message: 'Đã gửi yêu cầu. Chờ kỹ sư chấp thuận để mở liên hệ.', type: 'success');
        } catch (\Throwable $exception) {
            $this->addError('contact', $exception->getMessage());
        }
    }

    public function render()
    {
        $criteria = app(JobDescriptionParser::class)->parse($this->jobDescription);
        if ($this->discipline !== '') $criteria['discipline'] = $this->discipline;
        if ($this->minYears > 0) $criteria['years'] = $this->minYears;
        if ($this->skill !== '') {
            $criteria['skills'] = array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', $this->skill))));
        }

        $candidates = EngineerProfile::query()
            ->with(['cv' => fn ($query) => $query->where('status', 'published')])
            ->where('is_searchable', true)
            ->whereHas('cv', fn ($query) => $query->where('status', 'published'))
            ->when($criteria['discipline'] ?? null, fn ($query, $value) => $query->whereRaw('LOWER(discipline) LIKE ?', ['%'.strtolower($value).'%']))
            ->when($criteria['years'] ?? 0, fn ($query, $value) => $query->where('years_experience', '>=', $value))
            ->when($this->workMode !== '', fn ($query) => $query->where('work_mode', $this->workMode))
            ->when($this->availability !== '', fn ($query) => $query->where('availability', $this->availability))
            ->latest('updated_at')
            ->limit(60)
            ->get()
            ->filter(fn (EngineerProfile $profile) => $profile->cv)
            ->map(function (EngineerProfile $profile) use ($criteria): array {
                $match = app(CandidateMatcher::class)->score($criteria, $profile->cv);
                $skills = collect($profile->cv->skills())->map(fn ($skill) => is_array($skill) ? ($skill['name'] ?? '') : $skill)->filter()->take(8)->values()->all();
                return [
                    'id' => $profile->user_id,
                    'code' => $profile->anonymized_code,
                    'headline' => $profile->headline ?: 'Kỹ sư BIM/MEP',
                    'discipline' => $profile->discipline,
                    'years' => $profile->years_experience,
                    'location' => $profile->location,
                    'work_mode' => $profile->work_mode,
                    'availability' => $profile->availability,
                    'summary' => $profile->summary,
                    'skills' => $skills,
                    'score' => $match['score'],
                    'reasons' => $match['reasons'],
                    'experiences' => collect($profile->cv->experiences())->take(6)->values()->all(),
                    'projects' => data_get($profile->cv->data, 'projects', []),
                    'certifications' => data_get($profile->cv->data, 'certifications', []),
                ];
            })
            ->filter(function (array $candidate): bool {
                if ($this->skill === '') return true;
                $needle = strtolower($this->skill);
                return collect($candidate['skills'])->contains(fn ($skill) => str_contains(strtolower((string) $skill), $needle));
            })
            ->sortByDesc('score')
            ->values();

        $connections = RecruitmentContactRequest::query()
            ->where('recruiter_id', auth()->id())
            ->where('brand_id', brand()->id)
            ->with(['engineer.engineerProfile', 'conversation'])
            ->latest()
            ->get();

        $creditSummary = RecruiterEntitlement::query()
            ->where('recruiter_id', auth()->id())
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get()
            ->reduce(fn (array $summary, $item) => [
                'available' => $summary['available'] + $item->availableCredits(),
                'reserved' => $summary['reserved'] + (int) $item->credits_reserved,
                'used' => $summary['used'] + (int) $item->credits_used,
            ], ['available' => 0, 'reserved' => 0, 'used' => 0]);

        $selected = $this->selectedCandidate ? $candidates->firstWhere('id', $this->selectedCandidate) : null;

        return view('livewire.recruiter-dashboard', compact('candidates', 'criteria', 'connections', 'creditSummary', 'selected'))
            ->layout('layouts.recruiter', ['title' => 'Tìm ứng viên BIM/MEP']);
    }
}
