<?php

namespace App\Filament\Pages\Reports;

use App\Models\Project;
use App\Services\ProjectTransactionReportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

// class ProjectTransactionReport extends Page
// {
//     protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
//     protected static string $view = 'filament.pages.reports.project-transaction-report';
//     protected static ?string $navigationGroup = 'Financial Reports';
//     protected static ?string $title = 'Project Transaction Report';
//     protected static ?int $navigationSort = 3;

//     public ?array $reportData = null;
//     public ?array $filters = [];

//     // Form properties
//     public $from_date;
//     public $to_date;
//     public $project_id;

//     public function mount(): void
//     {
//         $this->form->fill([
//             'from_date' => Carbon::now()->startOfYear()->subYear()->format('Y-m-d'),
//             'to_date' => Carbon::now()->format('Y-m-d'),
//             'project_id' => null,
//         ]);

//         $this->generateReport();
//     }

//     public function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 Forms\Components\Section::make('Report Filters')
//                     ->schema([
//                         Forms\Components\Grid::make()
//                             ->schema([
//                                 Forms\Components\DatePicker::make('from_date')
//                                     ->label('From Date')
//                                     ->required()
//                                     ->helperText('Select the start date for the report period'),

//                                 Forms\Components\DatePicker::make('to_date')
//                                     ->label('To Date')
//                                     ->required()
//                                     ->helperText('Select the end date for the report period'),
//                             ])
//                             ->columns(2),

//                         Forms\Components\Select::make('project_id')
//                             ->label('Project')
//                             ->options(Project::pluck('title', 'id'))
//                             ->searchable()
//                             ->placeholder('All Projects')
//                             ->helperText('Select a specific project or leave blank to view data for all projects'),

//                         Forms\Components\Actions::make([
//                             Forms\Components\Actions\Action::make('generate')
//                                 ->label('Generate Report')
//                                 ->action('generateReport')
//                                 ->color('primary'),

//                             Forms\Components\Actions\Action::make('export_csv')
//                                 ->label('Export CSV')
//                                 ->action('exportCsv')
//                                 ->color('success'),

//                             Forms\Components\Actions\Action::make('export_pdf')
//                                 ->label('Export PDF')
//                                 ->action('exportPdf')
//                                 ->color('warning'),
//                         ]),
//                     ])
//                     ->columns(4),
//             ]);
//     }

//     public function generateReport(): void
//     {
//         $formData = $this->form->getState();

//         // Convert form data to the expected filter format
//         $filters = [
//             'date_range' => [
//                 'from' => $formData['from_date'],
//                 'until' => $formData['to_date'],
//             ],
//             'project_id' => $formData['project_id'],
//         ];

//         $this->filters = $filters;

//         $projectTransactionReportService = app(ProjectTransactionReportService::class);
//         $this->reportData = $projectTransactionReportService->generateProjectTransactionReport($filters);
//     }

//     /**
//      * Export the report to CSV format.
//      */
//     public function exportCsv()
//     {
//         $this->generateReport();

//         $filename = 'project_transaction_report_' . date('Y-m-d_H-i-s') . '.csv';
//         $headers = [
//             'Content-Type' => 'text/csv',
//             'Content-Disposition' => "attachment; filename=\"{$filename}\"",
//         ];

//         $callback = function () {
//             $file = fopen('php://output', 'w');

//             // Add headers
//             fputcsv($file, ['Month', 'Total Revenue', 'Total Expenses', 'Net Cash Flow']);

//             // Add data
//             foreach ($this->reportData['monthly_data'] as $data) {
//                 fputcsv($file, [
//                     $data['month_name'],
//                     $data['total_revenue'],
//                     $data['total_expenses'],
//                     $data['net_cash_flow'],
//                 ]);
//             }

//             // Add summary
//             fputcsv($file, ['']);
//             fputcsv($file, ['Summary']);
//             fputcsv($file, ['Total Revenue', $this->reportData['summary']['total_revenue']]);
//             fputcsv($file, ['Total Expenses', $this->reportData['summary']['total_expenses']]);
//             fputcsv($file, ['Net Cash Flow', $this->reportData['summary']['net_cash_flow']]);

//             // Add category breakdowns
//             fputcsv($file, ['']);
//             fputcsv($file, ['Revenue Categories']);
//             foreach ($this->reportData['category_totals']['revenue_categories'] as $category => $amount) {
//                 fputcsv($file, [$category, $amount]);
//             }

//             fputcsv($file, ['']);
//             fputcsv($file, ['Expense Categories']);
//             foreach ($this->reportData['category_totals']['expense_categories'] as $category => $amount) {
//                 fputcsv($file, [$category, $amount]);
//             }

//             fclose($file);
//         };

//         return response()->stream($callback, 200, $headers);
//     }

//     /**
//      * Export the report to PDF format.
//      */
//     public function exportPdf()
//     {
//         $this->generateReport();

//         // This would typically use a PDF library like DomPDF or TCPDF
//         // For now, we'll just redirect back with a message
//         $this->redirect(url()->current());
//         session()->flash('message', 'PDF export functionality will be implemented soon.');
//     }
// }
