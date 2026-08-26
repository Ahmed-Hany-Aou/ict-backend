<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupExpiredNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired notifications...');

        $expired = Notification::where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->count();

        if ($expired === 0) {
            $this->info('No expired notifications found.');
            return Command::SUCCESS;
        }

        Notification::where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->delete();

        $this->info("Cleaned up {$expired} expired notifications.");

        return Command::SUCCESS;
    }
}
