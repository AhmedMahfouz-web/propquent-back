<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class CompanyFinancialReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.company-financial-report';
    protected static ?string $navigationGroup = 'Financial Reports';
    protected static ?string $title = 'Company Financial Report';
    protected static ?int $navigationSort = 3;

    public function mount(Request $request)
    {
        // Handle refresh evaluations request
        if ($request->isMethod('POST') && $request->input('action') === 'refresh_evaluations') {
            $this->refreshEvaluations($request);
        }
    }

    private function refreshEvaluations(Request $request)
    {
        try {
            $startMonth = $request->input('start_month');
            $endMonth = $request->input('end_month');

            // Determine the date range for recalculation
            $params = ['--force' => true];
            
            if ($startMonth) {
                $params['--from-month'] = $startMonth;
            }
            
            if ($endMonth) {
                $params['--to-month'] = $endMonth;
            }

            // Run the evaluation calculation command
            Artisan::call('evaluations:calculate', $params);

            Notification::make()
                ->title('Evaluations Refreshed')
                ->body('Asset evaluations have been recalculated successfully.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Refresh Failed')
                ->body('Failed to refresh evaluations: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        // Redirect back to avoid form resubmission
        return redirect()->route('filament.admin.pages.company-financial-report', $request->only(['start_month', 'end_month']));
    }
}
