<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'activity_type',
        'slide_id',
        'quiz_id',
        'chapter_id',
        'duration',
        'metadata',
        'activity_timestamp',
    ];

    protected $casts = [
        'duration' => 'integer',
        'metadata' => 'array',
        'activity_timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Activity type constants
    const TYPE_SLIDE_VIEWED = 'slide_viewed';
    const TYPE_SLIDE_COMPLETED = 'slide_completed';
    const TYPE_QUIZ_STARTED = 'quiz_started';
    const TYPE_QUIZ_COMPLETED = 'quiz_completed';
    const TYPE_CHAPTER_STARTED = 'chapter_started';
    const TYPE_CHAPTER_COMPLETED = 'chapter_completed';
    const TYPE_HEARTBEAT = 'heartbeat';

    /**
     * Get the user that performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the slide associated with the activity
     */
    public function slide(): BelongsTo
    {
        return $this->belongsTo(Slide::class);
    }

    /**
     * Get the quiz associated with the activity
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the chapter associated with the activity
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Log a user activity
     */
    public static function logActivity(
        int $userId,
        string $activityType,
        ?int $slideId = null,
        ?int $quizId = null,
        ?int $chapterId = null,
        ?int $duration = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'slide_id' => $slideId,
            'quiz_id' => $quizId,
            'chapter_id' => $chapterId,
            'duration' => $duration,
            'metadata' => $metadata,
            'activity_timestamp' => now(),
        ]);
    }
}
