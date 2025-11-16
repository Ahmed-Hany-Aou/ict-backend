<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Services\CacheService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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

        // Use CacheService to reliably clear user-specific caches
        CacheService::clearUserCache($userId);
    }
}
