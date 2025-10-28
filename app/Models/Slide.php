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
        'meeting_datetime' => 'datetime',
    ];

    /**
     * Get the content attribute.
     * Handles both double-encoded JSON (from old data) and regular JSON.
     */
    public function getContentAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        // First decode (from database JSON field)
        $decoded = json_decode($value, true);

        // Check if it's double-encoded (the result is a string, not an array)
        if (is_string($decoded)) {
            // Decode again to get the actual array
            $decoded = json_decode($decoded, true);
        }

        return $decoded;
    }

    /**
     * Set the content attribute.
     */
    public function setContentAttribute($value)
    {
        // Store as regular JSON (not double-encoded)
        $this->attributes['content'] = json_encode($value);
    }

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