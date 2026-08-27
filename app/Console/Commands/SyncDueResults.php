<?php

namespace App\Console\Commands;

use App\Models\Race;
use Illuminate\Console\Command;

class SyncDueResults extends Command
{
    protected $signature = 'f1:sync-due-results';

    protected $description = 'Lancia f1:sync-results per tutte le gare già iniziate ma non ancora marcate come completate';

    public function handle(): int
    {
        $dueRaces = Race::where('status', '!=', 'completed')
            ->where('starts_at', '<=', now())
            ->get();

        foreach ($dueRaces as $race) {
            $this->call(SyncResults::class, ['race' => $race->id]);
        }

        return self::SUCCESS;
    }
}
