<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'sent_to',
        'sent_to_users',
        'created_by',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_to_users' => 'array',
        'expires_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    // Helper method to get recipients
    public function getRecipients()
    {
        if ($this->sent_to === 'all') {
            return User::where('role', 'student')->pluck('id');
        }

        return $this->sent_to_users ?? [];
    }
}
