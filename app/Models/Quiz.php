<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id', 'category', 'title', 'description', 'questions', 'passing_score', 'is_active', 'is_premium', 'publish_at'
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'publish_at' => 'datetime'
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function results()
    {
        return $this->hasMany(QuizResult::class);
    }

    /**
     * Check if quiz is actually live (active and publish date has passed)
     */
    public function isLive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->publish_at === null) {
            return true;
        }

        return $this->publish_at->isPast();
    }

    /**
     * Check if quiz is scheduled (active but publish date is in future)
     */
    public function isScheduled(): bool
    {
        return $this->is_active && $this->publish_at !== null && $this->publish_at->isFuture();
    }

    /**
     * Get the publish status as a string for admin UI
     */
    public function getPublishStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->isScheduled()) {
            return 'scheduled';
        }

        return 'active';
    }
}
