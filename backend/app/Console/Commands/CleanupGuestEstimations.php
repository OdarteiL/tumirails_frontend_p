<?php

namespace App\Console\Commands;

use App\Models\Estimation;
use Illuminate\Console\Command;

class CleanupGuestEstimations extends Command
{
    protected $signature = 'estimations:cleanup-guests';

    protected $description = 'Deletes expired guest estimations.';

    public function handle()
    {
        $deletedCount = Estimation::whereNotNull('reference_code')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Deleted {$deletedCount} expired guest estimations.");
    }
}
