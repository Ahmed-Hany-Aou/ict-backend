<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use App\Services\CacheService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Clear all quiz-related caches when quiz is deleted
                    CacheService::clearQuizCaches();
                }),
        ];
    }

    /**
     * Clear all quiz-related caches after saving
     */
    protected function afterSave(): void
    {
        CacheService::clearQuizCaches();
    }
}
