<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    /**
     * Clear all quiz-related caches after creating
     */
    protected function afterCreate(): void
    {
        // Clear all quiz-related caches
        Cache::flush();
    }
}
