<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use App\Services\CacheService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    /**
     * Clear all quiz-related caches after creating
     */
    protected function afterCreate(): void
    {
        CacheService::clearQuizCaches();
    }
}
