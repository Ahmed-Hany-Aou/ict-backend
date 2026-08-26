<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use App\Services\CacheService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChapter extends CreateRecord
{
    protected static string $resource = ChapterResource::class;

    /**
     * Clear all chapter-related caches after creating
     */
    protected function afterCreate(): void
    {
        CacheService::clearChapterCaches();
    }
}
