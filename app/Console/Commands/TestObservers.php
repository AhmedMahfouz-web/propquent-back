<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectTransaction;
use App\Models\ValueCorrection;
use App\Models\Project;
use App\Models\MonthlyProjectEvaluation;

class TestObservers extends Command
{
    protected $signature = 'test:observers';
    protected $description = 'Test if observers are properly registered and working';

    public function handle()
    {
        $this->info('Testing Observer Registration...');

        // Test ProjectTransaction observer
        $this->info('1. Testing ProjectTransaction observer...');
        $transaction = ProjectTransaction::first();
        if ($transaction) {
            $this->line("   Found transaction: {$transaction->id} for project {$transaction->project_key}");
            
            // Check if evaluation exists for this project
            $evaluation = MonthlyProjectEvaluation::where('project_key', $transaction->project_key)->first();
            if ($evaluation) {
                $this->line("   ✅ Evaluation exists for project {$transaction->project_key}");
                $this->line("   Last updated: {$evaluation->updated_at}");
            } else {
                $this->line("   ⚠️  No evaluation found for project {$transaction->project_key}");
            }
        } else {
            $this->line("   No transactions found to test");
        }

        // Test ValueCorrection observer
        $this->info('2. Testing ValueCorrection observer...');
        $correction = ValueCorrection::first();
        if ($correction) {
            $this->line("   Found correction: {$correction->id} for project {$correction->project_key}");
        } else {
            $this->line("   No value corrections found to test");
        }

        // Test Project observer
        $this->info('3. Testing Project observer...');
        $project = Project::first();
        if ($project) {
            $this->line("   Found project: {$project->key} - {$project->title}");
            $this->line("   Status: {$project->status}");
        } else {
            $this->line("   No projects found to test");
        }

        // Check overall evaluation status
        $this->info('4. Overall evaluation status...');
        $totalEvaluations = MonthlyProjectEvaluation::count();
        $totalProjects = Project::count();
        $totalTransactions = ProjectTransaction::where('serving', 'asset')->count();
        
        $this->line("   Total evaluations: {$totalEvaluations}");
        $this->line("   Total projects: {$totalProjects}");
        $this->line("   Total asset transactions: {$totalTransactions}");

        if ($totalEvaluations === 0 && $totalTransactions > 0) {
            $this->warn('⚠️  You have asset transactions but no evaluations. Run: php artisan evaluations:calculate');
        } elseif ($totalEvaluations > 0) {
            $this->info('✅ Evaluations exist in the database');
        }

        return 0;
    }
}
