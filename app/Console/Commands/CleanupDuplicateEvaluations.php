<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonthlyProjectEvaluation;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateEvaluations extends Command
{
    protected $signature = 'evaluations:cleanup';
    protected $description = 'Remove duplicate monthly project evaluations';

    public function handle()
    {
        $this->info('Checking for duplicate evaluations...');

        // Find duplicates
        $duplicates = DB::table('monthly_project_evaluations')
            ->select('project_key', 'month_date', DB::raw('COUNT(*) as count'))
            ->groupBy('project_key', 'month_date')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found.');
            return 0;
        }

        $this->warn("Found {$duplicates->count()} duplicate groups:");

        foreach ($duplicates as $duplicate) {
            $this->line("  {$duplicate->project_key} - {$duplicate->month_date}: {$duplicate->count} records");
            
            // Keep only the latest record (by ID) and delete the rest
            $records = MonthlyProjectEvaluation::where('project_key', $duplicate->project_key)
                ->where('month_date', $duplicate->month_date)
                ->orderBy('id', 'desc')
                ->get();
            
            // Keep the first (latest) record, delete the rest
            $toDelete = $records->slice(1);
            foreach ($toDelete as $record) {
                $record->delete();
                $this->line("    Deleted duplicate ID: {$record->id}");
            }
        }

        $this->info('Cleanup completed!');
        return 0;
    }
}
