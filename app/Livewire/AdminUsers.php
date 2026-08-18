<?php

namespace App\Livewire;

use App\Mail\WelcomeMemberMail;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUsers extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    // ─── Tạo thành viên mới ───────────────────────────────
    public bool $showCreateModal = false;
    public string $newName = '';
    public string $newEmail = '';
    public string $newPassword = '';
    public string $newRole = 'member'; // member | mod | admin

    public function openCreateModal(): void
    {
        if (!Auth::user()?->is_admin) return;
        $this->reset(['newName', 'newEmail', 'newPassword']);
        $this->newRole = 'member';
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function generatePassword(): void
    {
        // Chuỗi mật khẩu mạnh: chữ hoa/thường, số, ký tự đặc biệt
        $this->newPassword = Str::password(14);
    }

    public function createUser(): void
    {
        if (!Auth::user()?->is_admin) return;

        $this->validate([
            'newName'     => 'required|min:2|max:50',
            'newEmail'    => 'required|email|unique:users,email',
            'newPassword' => 'required|min:8',
            'newRole'     => 'required|in:member,mod,admin',
        ], [], [
            'newName'     => 'họ tên',
            'newEmail'    => 'email',
            'newPassword' => 'mật khẩu',
            'newRole'     => 'vai trò',
        ]);

        // Sinh username duy nhất từ họ tên (transliterate Việt → ASCII)
        $ascii    = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', trim($this->newName));
        $username = preg_replace('/[^a-z0-9._]/', '', preg_replace('/\s+/', '.', $ascii));
        if ($username === '') {
            $username = 'user';
        }
        $base = $username;
        $i    = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        $user = User::create([
            'name'     => $this->newName,
            'email'    => $this->newEmail,
            'username' => $username,
            'password' => $this->newPassword, // cast 'hashed' tự băm
            'level'    => 1,
            'xp'       => 0,
            'aip'      => 0,
            'streak'   => 0,
        ]);

        // is_admin / is_moderator không nằm trong $fillable → set trực tiếp
        $user->is_admin     = $this->newRole === 'admin';
        $user->is_moderator = $this->newRole === 'mod';
        $user->save();

        // Admin cấp tài khoản → coi như đã xác minh, khỏi bắt verify email
        $user->markEmailAsVerified();

        Membership::create([
            'user_id'    => $user->id,
            'status'     => 'active',
            'plan'       => 'lifetime',
            'expires_at' => '2099-12-31',
        ]);

        // Email chào mừng kèm thông tin đăng nhập (không chặn việc tạo nếu mail lỗi).
        try {
            Mail::to($user->email)->send(new WelcomeMemberMail(
                name:      $user->name,
                email:     $user->email,
                password:  $this->newPassword,
                loginUrl:  route('login'),
                resetUrl:  route('password.request'),
                brandName: config('app.name'),
            ));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->showCreateModal = false;
        $this->reset(['newName', 'newEmail', 'newPassword']);
        $this->resetPage();
        $this->dispatch('toast', message: 'Đã tạo thành viên ' . $user->name, type: 'success');
    }

    public function toggleAdmin(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        $user = User::findOrFail($id);
        $user->is_admin = !$user->is_admin;
        $user->save();
    }

    public function toggleModerator(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        $user = User::findOrFail($id);
        $user->is_moderator = !$user->is_moderator;
        $user->save();
    }

    public function banUser(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        if ($id === Auth::id()) return; // Can't ban yourself
        $user = User::findOrFail($id);
        $membership = $user->membership;
        if ($membership) {
            $membership->update(['status' => 'banned']);
            $this->dispatch('toast', message: $user->name . ' đã bị ban', type: 'success');
        }
    }

    public function unbanUser(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        $user = User::findOrFail($id);
        $membership = $user->membership;
        if ($membership && $membership->status === 'banned') {
            $membership->update(['status' => 'active']);
        }
    }

    public function exportCsv(): ?StreamedResponse
    {
        if (!Auth::user()?->is_admin) return null;

        $filename = 'members-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Họ tên', 'Email'], ',', '"', '', "\n");

            foreach ($this->usersQuery()->select(['name', 'email'])->orderBy('name')->cursor() as $user) {
                fputcsv($handle, [$user->name, $user->email], ',', '"', '', "\n");
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function usersQuery(): Builder
    {
        $query = User::query();

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(fn($q) => $q->where('name', 'ilike', $term)->orWhere('email', 'ilike', $term)->orWhere('username', 'ilike', $term));
        }

        return $query;
    }

    public function render()
    {
        $query = $this->usersQuery()
            ->with('membership')
            ->withCount('posts');

        return view('livewire.admin-users', ['users' => $query->latest()->paginate(20)])
            ->layout('layouts.app', ['title' => 'Quản lý người dùng — Admin']);
    }
}
