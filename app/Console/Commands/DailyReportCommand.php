<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\VoidLog;
use App\Services\BomService;
use Illuminate\Console\Command;

class DailyReportCommand extends Command
{
    protected $signature = 'gusto:daily-report {--date= : Date to report on (default: yesterday)}';
    protected $description = 'Generate and display the daily summary report for the owner';

    public function __construct(private BomService $bom)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now()->subDay();

        $from = $date->copy()->startOfDay();
        $to   = $date->copy()->endOfDay();

        $this->info("═══════════════════════════════════════");
        $this->info("  GUSTO Daily Report — {$date->format('Y-m-d')}");
        $this->info("═══════════════════════════════════════");

        // Revenue
        $revenue = Order::where('status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->sum('total');

        $orderCount = Order::where('status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $voidCount = VoidLog::whereBetween('created_at', [$from, $to])->count();

        $lowStock = Ingredient::lowStock()->count();

        $this->line('');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Revenue',   '$' . number_format($revenue, 2)],
                ['Paid Orders',     $orderCount],
                ['Void Items',      $voidCount],
                ['Low Stock Items', $lowStock],
            ]
        );

        // BOM alerts
        $variance = $this->bom->getVarianceReport($from, $to);
        $flagged = array_filter($variance, fn ($r) => $r['is_flagged']);

        if (!empty($flagged)) {
            $this->line('');
            $this->error('⚠️ BOM Variance Alerts:');
            foreach ($flagged as $r) {
                $this->warn("  {$r['name']}: {$r['variance_pct']}% variance");
            }
        }

        if ($lowStock > 0) {
            $this->line('');
            $this->warn("⚠️ {$lowStock} ingredient(s) below reorder level — check stock.");
        }

        $this->line('');

        return self::SUCCESS;
    }
}
