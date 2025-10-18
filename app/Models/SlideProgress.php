<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlideProgress extends Model
{
    use HasFactory;

    protected $table = 'slide_progress';

    protected $fillable = [
        'user_id',
        'slide_id',
        'chapter_id',
        'completed',
        'last_viewed_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'last_viewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slide()
    {
        return $this->belongsTo(Slide::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}