<?php

namespace App\Filament\Resources\SlideResource\Pages;

use App\Filament\Resources\SlideResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditSlide extends EditRecord
{
    protected static string $resource = SlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Clear all caches when slide is deleted
                    Cache::flush();
                }),
        ];
    }

    /**
     * Clear all caches after saving (slide count affects chapter data)
     */
    protected function afterSave(): void
    {
        Cache::flush();
    }
}
