<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitializeEvaluations extends Command
{
    protected $signature = 'evaluations:initialize 
                          {--force : Force recalculation of existing evaluations}';

    protected $description = 'Initialize all evaluations and profits (run once after deployment)';

    public function handle()
    {
        $this->info('===========================================');
        $this->info('  INITIALIZING EVALUATIONS AND PROFITS');
        $this->info('===========================================');
        $this->newLine();

        $force = $this->option('force');

        // Step 1: Clear caches
        $this->info('Step 1/3: Clearing caches...');
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            $this->line('  ✓ Caches cleared successfully');
        } catch (\Exception $e) {
            $this->warn('  ⚠ Cache clearing warning: ' . $e->getMessage());
        }
        $this->newLine();

        // Step 2: Calculate evaluations
        $this->info('Step 2/3: Calculating monthly evaluations...');
        $this->line('  This may take a few minutes depending on data size...');
        
        try {
            $options = $force ? ['--force' => true] : [];
            $exitCode = Artisan::call('evaluations:calculate', $options);
            
            if ($exitCode === 0) {
                $this->line('  ✓ Evaluations calculated successfully');
            } else {
                $this->error('  ✗ Evaluation calculation failed');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('  ✗ Error: ' . $e->getMessage());
            return 1;
        }
        $this->newLine();

        // Step 3: Optimize application
        $this->info('Step 3/3: Optimizing application...');
        try {
            Artisan::call('optimize');
            $this->line('  ✓ Application optimized');
        } catch (\Exception $e) {
            $this->warn('  ⚠ Optimization warning: ' . $e->getMessage());
        }
        $this->newLine();

        $this->info('===========================================');
        $this->info('  INITIALIZATION COMPLETED SUCCESSFULLY!');
        $this->info('===========================================');
        $this->newLine();
        
        $this->info('Next Steps:');
        $this->line('  1. Verify data in your financial reports');
        $this->line('  2. Check the scheduled tasks: php artisan schedule:list');
        $this->line('  3. Set up cron job on Hostinger (see DEPLOYMENT_GUIDE.md)');
        $this->newLine();
        
        $this->comment('The system will now automatically update evaluations:');
        $this->line('  - Monthly: 1st of each month at 1:00 AM');
        $this->line('  - Daily: Every day at 2:00 AM');
        $this->newLine();

        return 0;
    }
}
