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
    ];

    protected $casts = [
        'content' => 'array',
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
}