<?php

namespace App\Services;

use App\Models\WareNotification;
use App\Models\WareUser;

class NotificationService
{
    /**
     * Send Notification to specific user
     */
    public static function send($userId, $title, $message, $type = 'info', $url = null)
    {
        return WareNotification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type, // info, warning, success, danger
            'url'     => $url
        ]);
    }

    /**
     * Send to all Admins (Helper)
     */
    public static function sendToAdmins($title, $message, $type = 'info', $url = null)
    {
        $admins = WareUser::whereHas('roles', function($q){
            $q->where('name', 'Super Admin')
              ->orWhere('name', 'VP Operations');
        })->get();

        foreach($admins as $admin) {
            self::send($admin->id, $title, $message, $type, $url);
        }
    }
}