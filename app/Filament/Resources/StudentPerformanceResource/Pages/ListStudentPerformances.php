<?php

namespace App\Filament\Resources\StudentPerformanceResource\Pages;

use App\Filament\Resources\StudentPerformanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\SlideProgress;
use App\Models\QuizResult;

class ListStudentPerformances extends ListRecords
{
    protected static string $resource = StudentPerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - students are managed elsewhere
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add summary widgets here
        ];
    }
}
