<?php

namespace App\Filament\Resources\CashflowResource\Pages;

use App\Filament\Resources\CashflowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class ListCashflows extends ListRecords
{
    protected static string $resource = CashflowResource::class;
    
    protected static string $view = 'filament.resources.cashflow-resource.pages.list-cashflows';

    public function mount(): void
    {
        parent::mount();

        // Set default date range filter if not already set
        if (empty($this->tableFilters['date_range']['from']) && empty($this->tableFilters['date_range']['until'])) {
            $this->tableFilters = array_merge($this->tableFilters ?? [], [
                'date_range' => [
                    'from' => Carbon::now()->format('Y-m-d'),
                    'until' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                ]
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh_cache')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    // Clear cashflow related cache
                    cache()->forget('current_cash_balance');
                    cache()->forget('cashflow_summary');

                    // Refresh the table data
                    $this->resetTable();

                    Notification::make()
                        ->title('Success')
                        ->body('Cashflow data refreshed successfully!')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Transactions'),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Pending')),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Completed')),
            'cash_in' => Tab::make('Cash In')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cash_in', '>', 0)),
            'cash_out' => Tab::make('Cash Out')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cash_out', '>', 0)),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CashflowResource\Widgets\CashflowOverviewWidget::class,
            CashflowResource\Widgets\MonthlyCashflowChartWidget::class,
        ];
    }
}
