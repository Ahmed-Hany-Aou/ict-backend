<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Clear user's cache when payment is deleted
                    $this->clearUserCache();
                }),
        ];
    }

    /**
     * Clear user-specific caches after saving payment
     * This is critical when payment status changes from pending to approved
     * as it affects the user's premium status and locked content
     */
    protected function afterSave(): void
    {
        $this->clearUserCache();
    }

    /**
     * Clear all caches for the user associated with this payment
     */
    private function clearUserCache(): void
    {
        $userId = $this->record->user_id;

        if (!$userId) {
            return;
        }

        // Clear user-specific chapter and quiz caches (both premium states)
        Cache::forget("chapters_list_user_{$userId}_premium_true");
        Cache::forget("chapters_list_user_{$userId}_premium_false");
        Cache::forget("user_progress_{$userId}");

        // Clear all chapter-specific caches for this user
        Cache::forget("chapters_published_with_scheduled");

        // Clear quiz caches (both premium states)
        Cache::forget("quizzes_all_with_scheduled_premium_true");
        Cache::forget("quizzes_all_with_scheduled_premium_false");
        Cache::forget("quizzes_active_with_scheduled_ordered");
    }
}
