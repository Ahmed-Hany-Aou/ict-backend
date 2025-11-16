<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Clear all quiz-related caches when quiz is deleted
                    $this->clearQuizCaches();
                }),
        ];
    }

    /**
     * Clear all quiz-related caches after saving
     */
    protected function afterSave(): void
    {
        $this->clearQuizCaches();
    }

    /**
     * Clear all quiz-related caches
     */
    private function clearQuizCaches(): void
    {
        // Clear all quiz caches
        Cache::flush();
    }
}
