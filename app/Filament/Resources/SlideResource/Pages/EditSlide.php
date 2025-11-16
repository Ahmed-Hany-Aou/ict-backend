<?php

namespace App\Filament\Resources\SlideResource\Pages;

use App\Filament\Resources\SlideResource;
use App\Services\CacheService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSlide extends EditRecord
{
    protected static string $resource = SlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Clear all caches when slide is deleted
                    CacheService::clearSlideCaches();
                }),
        ];
    }

    /**
     * Clear all caches after saving (slide count affects chapter data)
     */
    protected function afterSave(): void
    {
        CacheService::clearSlideCaches();
    }
}
