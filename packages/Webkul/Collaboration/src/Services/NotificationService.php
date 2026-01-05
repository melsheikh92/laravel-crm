<?php

namespace Webkul\Collaboration\Services;

use Webkul\Collaboration\Repositories\NotificationRepository;

class NotificationService
{
    public function __construct(
        protected NotificationRepository $notificationRepository
    ) {}

    public function create(array $data): \Webkul\Collaboration\Contracts\Notification
    {
        return $this->notificationRepository->create($data);
    }

    public function markAsRead(int $notificationId): void
    {
        $this->notificationRepository->update([
            'read_at' => now(),
        ], $notificationId);
    }

    public function getUserNotifications(int $userId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return $this->notificationRepository
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->notificationRepository
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}

