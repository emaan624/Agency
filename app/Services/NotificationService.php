<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function send(User $user, string $title, string $body = '', string $type = 'info', string $link = ''): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'link' => $link ?: null,
        ]);
    }

    public function markRead(UserNotification $notification): void
    {
        $notification->update(['read_at' => now()]);
    }

    public function markAllRead(User $user): void
    {
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function unreadCount(User $user): int
    {
        return $user->notifications()->whereNull('read_at')->count();
    }
}
