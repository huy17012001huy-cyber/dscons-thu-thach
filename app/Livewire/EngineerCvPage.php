<?php

namespace App\Livewire;

use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use App\Models\RecruitmentContactRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Modules\Recruitment\Application\EngineerCvService;
use Modules\Recruitment\Application\RecruiterContactService;

class EngineerCvPage extends Component
{
    public bool $isAdminPreview = false;

    public ?int $profileUserId = null;

    public string $profileEmail = '';

    public string $headline = '';

    public string $discipline = 'BIM';

    public string $summary = '';

    public int $yearsExperience = 0;

    public string $location = '';

    public string $workMode = 'Hybrid';

    public string $availability = 'Đang cập nhật';

    public string $skillsText = '';

    public string $experiencesText = '';

    public string $educationText = '';

    public string $projectsText = '';

    public string $certificationsText = '';

    public string $languagesText = '';

    public string $template = 'technical-clean';

    public string $accentColor = '#1F77BE';

    public string $contactPhone = '';

    public bool $showEmail = true;

    public bool $showPhone = false;

    public bool $isPublished = false;

    public function mount(?EngineerCv $cv = null): void
    {
        $this->isAdminPreview = request()->routeIs('community.manage.recruitment.preview.cv');
        $user = auth()->user();
        abort_unless($user instanceof User && ($this->isAdminPreview ? $user->isCommunityAdmin(brand()->id) : $user->isEngineer()), 403);
        abort_unless(brand()->has_cv || (app()->environment('testing') && brand()->slug === 'dscons'), 404);

        if ($this->isAdminPreview) {
            abort_unless($cv instanceof EngineerCv && $cv->brand_id === brand()->id, 404);
            $this->profileUserId = (int) $cv->user_id;
            $profile = EngineerProfile::query()->where('user_id', $cv->user_id)->first();
        } else {
            $this->profileUserId = (int) $user->id;
            $workspace = app(EngineerCvService::class)->ensureWorkspace($user);
            $profile = $workspace['profile'];
            $cv = $workspace['cv'];
        }

        $profile ??= new EngineerProfile;

        $this->fill([
            'profileEmail' => $profile->contact_email ?? ($cv->user instanceof User ? $cv->user->email : $user->email),
            'headline' => $profile->headline ?? '',
            'discipline' => $profile->discipline ?? 'BIM',
            'summary' => $profile->summary ?? '',
            'yearsExperience' => (int) ($profile->years_experience ?? 0),
            'location' => $profile->location ?? '',
            'workMode' => $profile->work_mode ?? 'Hybrid',
            'availability' => $profile->availability ?? 'Đang cập nhật',
            'contactPhone' => $profile->contact_phone ?? '',
            'showEmail' => data_get($profile->contact_visibility ?? [], 'email', true),
            'showPhone' => data_get($profile->contact_visibility ?? [], 'phone', false),
            'template' => $cv->template ?? 'technical-clean',
            'accentColor' => $cv->accent_color ?? '#1F77BE',
            'isPublished' => $cv->status === 'published',
        ]);

        $this->skillsText = $this->joinItems($cv->skills(), fn ($item) => is_array($item) ? ($item['name'] ?? '') : $item, ', ');
        $this->experiencesText = $this->joinItems(data_get($cv->data, 'experiences', []), fn ($item) => is_array($item) ? implode(' · ', array_filter([$item['role'] ?? '', $item['project'] ?? '', $item['summary'] ?? ''])) : $item);
        $this->educationText = $this->joinItems(data_get($cv->data, 'education', []), fn ($item) => is_array($item) ? implode(' · ', array_filter([$item['school'] ?? '', $item['course'] ?? ''])) : $item);
        $this->projectsText = $this->joinItems(data_get($cv->data, 'projects', []), fn ($item) => is_array($item) ? implode(' · ', array_filter([$item['name'] ?? '', $item['role'] ?? '', $item['summary'] ?? ''])) : $item);
        $this->certificationsText = $this->joinItems(data_get($cv->data, 'certifications', []), fn ($item) => is_array($item) ? implode(' · ', array_filter([$item['name'] ?? '', $item['issuer'] ?? '', $item['year'] ?? ''])) : $item);
        $this->languagesText = $this->joinItems(data_get($cv->data, 'languages', []), fn ($item) => is_array($item) ? implode(' · ', array_filter([$item['name'] ?? '', $item['level'] ?? ''])) : $item, ', ');
    }

    public function save(bool $publish = false): void
    {
        abort_if($this->isAdminPreview, 403);
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isEngineer(), 403);
        $this->validate([
            'headline' => 'required|string|max:180',
            'discipline' => 'required|string|max:120',
            'summary' => 'nullable|string|max:3000',
            'yearsExperience' => 'integer|min:0|max:60',
            'location' => 'nullable|string|max:120',
            'workMode' => 'required|string|max:40',
            'availability' => 'required|string|max:40',
            'skillsText' => 'required|string|max:1000',
            'experiencesText' => 'nullable|string|max:5000',
            'educationText' => 'nullable|string|max:2000',
            'projectsText' => 'nullable|string|max:5000',
            'certificationsText' => 'nullable|string|max:3000',
            'languagesText' => 'nullable|string|max:500',
            'contactPhone' => 'nullable|string|max:40',
        ]);

        $isPublished = $publish || $this->isPublished;
        $data = [
            'headline' => $this->headline,
            'discipline' => $this->discipline,
            'summary' => $this->summary,
            'years_experience' => $this->yearsExperience,
            'location' => $this->location,
            'work_mode' => $this->workMode,
            'availability' => $this->availability,
            'skills' => $this->lines($this->skillsText, true),
            'experiences' => $this->lines($this->experiencesText, false, 'role'),
            'education' => $this->lines($this->educationText, false, 'school'),
            'projects' => $this->lines($this->projectsText),
            'certifications' => $this->lines($this->certificationsText),
            'languages' => $this->lines($this->languagesText, true),
        ];

        app(EngineerCvService::class)->save($user, $isPublished, [
            'headline' => $this->headline,
            'discipline' => $this->discipline,
            'summary' => $this->summary,
            'years_experience' => $this->yearsExperience,
            'location' => $this->location,
            'work_mode' => $this->workMode,
            'availability' => $this->availability,
            'contact_phone' => $this->contactPhone,
            'contact_visibility' => ['email' => $this->showEmail, 'phone' => $this->showPhone],
        ], [
            'title' => 'CV '.$this->headline,
            'template' => $this->template,
            'accent_color' => $this->accentColor,
            'data' => $data,
        ]);

        $this->isPublished = $isPublished;
        $this->dispatch('toast', message: $isPublished ? 'CV đã được cập nhật và công khai tìm kiếm.' : 'Đã lưu bản nháp CV.', type: 'success');
    }

    public function acceptRequest(int $requestId): void
    {
        abort_if($this->isAdminPreview, 403);
        $request = $this->incomingRequests()->findOrFail($requestId);
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        app(RecruiterContactService::class)->respond($request, $user, true);
    }

    public function rejectRequest(int $requestId): void
    {
        abort_if($this->isAdminPreview, 403);
        $request = $this->incomingRequests()->findOrFail($requestId);
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        app(RecruiterContactService::class)->respond($request, $user, false);
    }

    /** @return Builder<RecruitmentContactRequest> */
    private function incomingRequests(): Builder
    {
        return RecruitmentContactRequest::query()
            ->where('brand_id', brand()->id)
            ->where('engineer_id', $this->profileUserId)
            ->where('status', 'pending')
            ->with('recruiter.recruiterProfile');
    }

    /** @return array<int, array<string, string>> */
    private function lines(string $value, bool $commaSeparated = false, string $key = 'name'): array
    {
        $separator = $commaSeparated ? '/[,\n]+/' : '/\r?\n/';
        $lines = preg_split($separator, $value) ?: [];

        return collect($lines)
            ->map(fn ($line) => trim($line))
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($line) => [$key => $line])
            ->values()
            ->all();
    }

    /** @param array<int, mixed> $items @param callable(mixed): string $mapper */
    private function joinItems(array $items, callable $mapper, string $separator = "\n"): string
    {
        return collect($items)->map($mapper)->filter()->implode($separator);
    }

    public function render(): View
    {
        return view('livewire.engineer-cv-page', [
            'incomingRequests' => $this->incomingRequests()->latest()->get(),
        ])->layout('layouts.app', ['title' => 'CV kỹ sư · '.brand()->name]);
    }
}
