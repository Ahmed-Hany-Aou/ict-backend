<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'chapter_number', 'content', 'video_url', 'is_published', 'is_premium'
    ];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
        'is_premium' => 'boolean'
    ];

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function userProgress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function sessionVideos()
    {
        return $this->hasMany(SessionVideo::class);
    }
}
