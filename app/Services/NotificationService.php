<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function createNotification(array $data)
    {
        $notification = Notification::create($data);

        // Distribute to users
        $this->distributeNotification($notification);

        return $notification;
    }

    public function distributeNotification(Notification $notification)
    {
        $userIds = $notification->getRecipients();

        $userNotifications = [];
        foreach ($userIds as $userId) {
            $userNotifications[] = [
                'user_id' => $userId,
                'notification_id' => $notification->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert for performance
        if (!empty($userNotifications)) {
            UserNotification::insert($userNotifications);
        }
    }
}
