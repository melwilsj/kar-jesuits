<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ModelSnapshot;

class CleanupModelSnapshots extends Command
{
    protected $signature = 'snapshots:cleanup {--days=90}';
    protected $description = 'Cleanup old model snapshots';

    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);

        $count = ModelSnapshot::where('snapshot_time', '<', $date)->delete();
        
        $this->info("Deleted {$count} old snapshots");
    }
} 