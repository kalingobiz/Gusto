<?php

namespace App\Console\Commands;

use App\Services\BomService;
use Illuminate\Console\Command;

class CheckVarianceCommand extends Command
{
    protected $signature = 'gusto:check-variance {--days=30 : Number of days to look back}';
    protected $description = 'Check BOM variance and flag ingredients exceeding the 5% threshold';

    public function __construct(private BomService $bom)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $from = now()->subDays($days)->startOfDay();
        $to   = now()->endOfDay();

        $this->info("Checking BOM variance for last {$days} days...");

        $report = $this->bom->getVarianceReport($from, $to);
        $flagged = array_filter($report, fn ($row) => $row['is_flagged']);

        if (empty($flagged)) {
            $this->info('✓ No variance alerts. All ingredients within acceptable range.');
            return self::SUCCESS;
        }

        $this->error('⚠️ VARIANCE ALERTS DETECTED:');
        $headers = ['Ingredient', 'Unit', 'Expected Used', 'Variance', 'Variance %'];
        $rows = array_map(fn ($r) => [
            $r['name'], $r['unit'],
            number_format($r['theoretical_used'], 4),
            number_format($r['variance'], 4),
            $r['variance_pct'] . '%',
        ], $flagged);

        $this->table($headers, $rows);
        $this->warn(count($flagged) . ' ingredient(s) flagged. Review the BOM Variance report in the admin panel.');

        return self::FAILURE;
    }
}
