<?php

namespace App\Console\Commands;

use App\Models\Estimation;
use Illuminate\Console\Command;

class NormalizeEstimationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:normalize-estimations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize appliances_snapshot in estimations (convert appliance_id -> id)';

    public function handle(): int
    {
        $this->info('Scanning estimations for legacy appliance snapshot keys...');

        $count = 0;
        $updated = 0;

        Estimation::cursor()->each(function (Estimation $estimation) use (&$count, &$updated) {
            $count++;

            $snapshot = $estimation->appliances_snapshot ?? [];

            if (! is_array($snapshot) || empty($snapshot)) {
                return;
            }

            $needsUpdate = false;
            $normalized = [];

            foreach ($snapshot as $item) {
                // if legacy key exists, map to 'id'
                if (is_array($item) && array_key_exists('appliance_id', $item) && ! array_key_exists('id', $item)) {
                    $item['id'] = $item['appliance_id'];
                    unset($item['appliance_id']);
                    $needsUpdate = true;
                }

                $normalized[] = $item;
            }

            if ($needsUpdate) {
                $estimation->appliances_snapshot = $normalized;
                $estimation->saveQuietly();
                $updated++;
                $this->line("Updated estimation id={$estimation->id}");
            }
        });

        $this->info("Scanned {$count} estimations; updated {$updated} records.");

        return 0;
    }
}
