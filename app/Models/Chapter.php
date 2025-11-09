<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'chapter_number', 'content', 'video_url', 'video_type', 'meeting_link', 'meeting_datetime', 'is_published', 'is_premium', 'publish_at'
    ];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
        'is_premium' => 'boolean',
        'meeting_datetime' => 'datetime',
        'publish_at' => 'datetime'
    ];

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

   // public function userProgress()
    //{
      //  return $this->hasMany(UserProgress::class);
    //}

    public function sessionVideos()
    {
        return $this->hasMany(SessionVideo::class);
    }

    public function slides()
{
    return $this->hasMany(Slide::class)->orderBy('slide_number');
}

public function userProgress($userId)
{

    return $this->hasOne(UserProgress::class)->where('user_id', $userId);
}

/**
 * Check if chapter is actually live (published and publish date has passed)
 */
public function isLive(): bool
{
    if (!$this->is_published) {
        return false;
    }

    if ($this->publish_at === null) {
        return true;
    }

    return $this->publish_at->isPast();
}

/**
 * Check if chapter is scheduled (published but publish date is in future)
 */
public function isScheduled(): bool
{
    return $this->is_published && $this->publish_at !== null && $this->publish_at->isFuture();
}

/**
 * Get the publish status as a string for admin UI
 */
public function getPublishStatus(): string
{
    if (!$this->is_published) {
        return 'unpublished';
    }

    if ($this->isScheduled()) {
        return 'scheduled';
    }

    return 'published';
}
}
