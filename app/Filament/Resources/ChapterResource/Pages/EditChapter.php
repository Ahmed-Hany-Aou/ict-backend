<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditChapter extends EditRecord
{
    protected static string $resource = ChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Clear all chapter-related caches when chapter is deleted
                    $this->clearChapterCaches();
                }),
        ];
    }

    /**
     * Clear all chapter-related caches after saving
     */
    protected function afterSave(): void
    {
        $this->clearChapterCaches();
    }

    /**
     * Clear all chapter-related caches
     */
    private function clearChapterCaches(): void
    {
        // Clear all chapter list caches (for all users and premium statuses)
        Cache::flush(); // This clears all caches - use with caution

        // Alternative: Clear specific cache patterns
        // Cache::forget('chapters_published_with_scheduled');
        // Cache::forget('platform_stats');

        // Note: User-specific caches will be regenerated on next request
    }
}
