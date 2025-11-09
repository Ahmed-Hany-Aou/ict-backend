<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateChapter extends CreateRecord
{
    protected static string $resource = ChapterResource::class;

    /**
     * Clear all chapter-related caches after creating
     */
    protected function afterCreate(): void
    {
        // Clear all chapter-related caches
        Cache::flush();
    }
}
