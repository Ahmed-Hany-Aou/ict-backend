<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use App\Services\CacheService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChapter extends EditRecord
{
    protected static string $resource = ChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Clear all chapter-related caches when chapter is deleted
                    CacheService::clearChapterCaches();
                }),
        ];
    }

    /**
     * Clear all chapter-related caches after saving
     */
    protected function afterSave(): void
    {
        CacheService::clearChapterCaches();
    }
}
