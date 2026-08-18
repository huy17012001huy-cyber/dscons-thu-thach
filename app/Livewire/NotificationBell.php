<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $showDropdown = false;

    public function toggleDropdown(): void
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function markAllRead(): void
    {
        Auth::user()?->unreadNotifications->markAsRead();
        $this->showDropdown = false;
    }

    public function openNotification(string $id): void
    {
        $notification = Auth::user()->notifications()->find($id);
        if (!$notification) return;

        $notification->markAsRead();
        $this->showDropdown = false;

        $postId = $notification->data['post_id'] ?? null;
        $url = $notification->data['url'] ?? null;

        if ($postId) {
            $this->redirect(route('feed', ['post' => (int) $postId]), navigate: true);
        } elseif ($url && $this->isSafeUrl($url)) {
            $this->redirect($url, navigate: true);
        }
    }

    private function isSafeUrl(string $url): bool
    {
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? null;
        $host = $parsed['host'] ?? null;

        // Block non-http schemes (javascript:, data:, etc.)
        if ($scheme && !in_array($scheme, ['http', 'https'], true)) return false;

        // Allow relative URLs or same-host URLs
        return !$host || $host === request()->getHost();
    }

    public function render()
    {
        $user = Auth::user();
        $count = $user?->unreadNotifications()->count() ?? 0;
        $notifications = $this->showDropdown
            ? $user?->notifications()->latest()->take(20)->get() ?? collect()
            : collect();

        return view('livewire.notification-bell', [
            'count' => $count,
            'notifications' => $notifications,
        ]);
    }
}
