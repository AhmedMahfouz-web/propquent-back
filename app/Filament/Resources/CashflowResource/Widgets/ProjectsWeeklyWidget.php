<?php

namespace App\Filament\Resources\CashflowResource\Widgets;

use App\Filament\Resources\CashflowResource;
use App\Models\Project;
use App\Models\ProjectTransaction;
use Filament\Widgets\Widget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectsWeeklyWidget extends Widget
{
    protected static string $view = 'filament.widgets.projects-weekly';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 3;
    
    public function getViewData(): array
    {
        $weeks = $this->getWeeklyData();
        $projects = $this->getOngoingProjects();
        
        return [
            'weeks' => $weeks,
            'projects' => $projects,
        ];
    }
    
    private function getWeeklyData(): array
    {
        $startDate = now()->startOfWeek();
        $weeks = [];
        
        // Get 12 weeks of data (3 months)
        for ($i = 0; $i < 12; $i++) {
            $weekStart = $startDate->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();
            
            // Calculate expected cash in hand for this week
            $expectedCashInHand = $this->calculateExpectedCashForWeek($weekStart, $weekEnd);
            
            $weeks[] = [
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'week_label' => $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
                'expected_cash' => $expectedCashInHand,
            ];
        }
        
        return $weeks;
    }
    
    private function calculateExpectedCashForWeek($weekStart, $weekEnd): float
    {
        // Start with current balance
        $currentBalance = CashflowResource::getCurrentCashBalance();
        
        // Add all transactions that should be completed by the end of this week
        $weeklyTransactions = DB::table('project_transactions')
            ->where('status', 'pending')
            ->where('due_date', '<=', $weekEnd)
            ->where('due_date', '>=', now())
            ->selectRaw('
                SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) as revenue,
                SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END) as expenses
            ')
            ->first();
        
        $revenue = $weeklyTransactions->revenue ?? 0;
        $expenses = $weeklyTransactions->expenses ?? 0;
        
        return $currentBalance + $revenue - $expenses;
    }
    
    private function getOngoingProjects(): array
    {
        // Get projects that have pending transactions
        $projects = Project::whereHas('transactions', function ($query) {
                $query->where('status', 'pending')
                      ->where('due_date', '>=', now())
                      ->where('due_date', '<=', now()->addWeeks(12));
            })
            ->with(['transactions' => function ($query) {
                $query->where('status', 'pending')
                      ->where('due_date', '>=', now())
                      ->where('due_date', '<=', now()->addWeeks(12))
                      ->orderBy('due_date');
            }])
            ->get();
        
        $projectsData = [];
        
        foreach ($projects as $project) {
            $installments = [];
            
            foreach ($project->transactions as $transaction) {
                $weekStart = Carbon::parse($transaction->due_date)->startOfWeek();
                $weekIndex = $weekStart->diffInWeeks(now()->startOfWeek());
                
                if ($weekIndex >= 0 && $weekIndex < 12) {
                    $installments[$weekIndex][] = [
                        'id' => $transaction->id,
                        'due_date' => $transaction->due_date,
                        'financial_type' => $transaction->financial_type,
                        'amount' => $transaction->amount,
                        'description' => $transaction->description ?? ucfirst($transaction->financial_type),
                    ];
                }
            }
            
            if (!empty($installments)) {
                $projectsData[] = [
                    'id' => $project->id,
                    'title' => $project->title,
                    'status' => $project->status,
                    'installments' => $installments,
                ];
            }
        }
        
        return $projectsData;
    }
}
