<?php

namespace App\Filament\Resources\SlideResource\Pages;

use App\Filament\Resources\SlideResource;
use App\Services\CacheService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSlide extends CreateRecord
{
    protected static string $resource = SlideResource::class;

    /**
     * Clear all caches after creating (slide count affects chapter data)
     */
    protected function afterCreate(): void
    {
        CacheService::clearSlideCaches();
    }
}
