<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use App\Core\Auth\ProfileUpdateService;

class ProfileEditPage extends Component
{
    use WithFileUploads;

    public string $editName = '';

    public string $editUsername = '';

    public string $editBio = '';

    public string $location = '';

    public ?TemporaryUploadedFile $avatarUpload = null;

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
        $user = $this->currentUser();
        $this->fill([
            'editName' => $user->name,
            'editUsername' => $user->username ?: Str::slug($user->name, '-'),
            'editBio' => $user->bio ?? '',
            'location' => $user->location ?? '',
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'editName' => 'required|string|min:2|max:50',
            'editUsername' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[a-z0-9_-]+$/', 'unique:users,username,'.Auth::id()],
            'editBio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:160',
        ], [
            'editUsername.regex' => 'Handle chỉ gồm chữ thường, số, dấu gạch ngang hoặc gạch dưới.',
            'editUsername.unique' => 'Handle này đã được sử dụng.',
        ]);

        $key = 'save-profile:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('editName', 'Bạn cập nhật quá nhanh. Vui lòng thử lại sau.');

            return;
        }

        $user = $this->currentUser();
        app(ProfileUpdateService::class)->update($user, [
            'name' => trim($this->editName),
            'username' => strtolower(trim($this->editUsername)),
            'bio' => trim($this->editBio) ?: null,
            'location' => trim($this->location) ?: null,
        ]);
        RateLimiter::hit($key, 3600);

        session()->flash('profile_saved', 'Đã cập nhật hồ sơ của bạn.');
        $this->redirect(route('profile', $user->username), navigate: true);
    }

    public function updatedAvatarUpload(): void
    {
        $this->validate(['avatarUpload' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        if (! $this->avatarUpload instanceof TemporaryUploadedFile) {
            return;
        }

        $user = $this->currentUser();
        app(ProfileUpdateService::class)->updateAvatar($user, $this->avatarUpload);
        $this->dispatch('toast', message: 'Đã cập nhật ảnh đại diện.', type: 'success');
    }

    public function cancel(): void
    {
        $user = $this->currentUser();
        $this->redirect(route('profile', $user->username ?: $user->id), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.profile-edit-page', ['user' => $this->currentUser()])
            ->layout('layouts.app', ['title' => 'Sửa hồ sơ · '.brand()->name]);
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
