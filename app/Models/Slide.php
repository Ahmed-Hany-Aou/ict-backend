<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'slide_number',
        'type',
        'content',
        'meeting_link',
        'video_url',
    ];

    protected $casts = [
        'content' => 'array',
        'meeting_datetime' => 'datetime',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function progress()
    {
        return $this->hasMany(SlideProgress::class);
    }

    public function userProgress($userId)
    {
        return $this->hasOne(SlideProgress::class)->where('user_id', $userId);
    }
    // Helper methods
    public function hasScheduledMeeting()
    {
        return $this->video_type === 'scheduled';
    }

    public function hasRecordedVideo()
    {
        return $this->video_type === 'recorded';
    }

    public function isMeetingUpcoming()
    {
        return $this->hasScheduledMeeting() && 
               $this->meeting_datetime && 
               $this->meeting_datetime->isFuture();
    }
}