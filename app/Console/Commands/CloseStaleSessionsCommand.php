<?php

namespace App\Console\Commands;

use App\Models\TableSession;
use Illuminate\Console\Command;

class CloseStaleSessionsCommand extends Command
{
    protected $signature = 'gusto:close-stale-sessions {--hours=12 : Sessions older than this are closed}';
    protected $description = 'Close table sessions that have been open for too long';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');

        $stale = TableSession::whereNull('closed_at')
            ->where('opened_at', '<', now()->subHours($hours))
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No stale sessions found.');
            return self::SUCCESS;
        }

        foreach ($stale as $session) {
            $session->close();
            $session->restaurantTable?->update(['status' => 'available']);
        }

        $this->info("Closed {$stale->count()} stale session(s) (older than {$hours}h).");

        return self::SUCCESS;
    }
}
