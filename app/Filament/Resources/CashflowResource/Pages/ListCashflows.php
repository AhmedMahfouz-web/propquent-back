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
    
    public $monthsFilter = 3;
    public $statusFilter = '';
    public $sortField = 'status';
    public $sortDirection = 'asc';
    public $weekSortField = null;
    public $weekSortDirection = 'desc';

    public function mount(): void
    {
        parent::mount();
    }

    public function loadData()
    {
        // Initialize data loading
    }

    public function filterTable()
    {
        // This method will trigger a re-render of the table with new filter values
        $this->dispatch('table-filtered');
    }

    public function getFilteredProjects()
    {
        $query = $this->getResource()::getEloquentQuery();
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        
        $projects = $query->get();
        
        // Apply sorting
        if ($this->weekSortField !== null) {
            // Sort by week transaction amounts
            $projects = $this->sortProjectsByWeek($projects);
        } else {
            // Sort by regular fields
            $projects = $this->sortProjectsByField($projects);
        }
        
        return $projects;
    }

    private function sortProjectsByField($projects)
    {
        return $projects->sort(function ($a, $b) {
            // Always prioritize active projects first unless sorting by status in desc
            $statusPriorityA = $a->status === 'active' ? 1 : ($a->status === 'pending' ? 2 : 3);
            $statusPriorityB = $b->status === 'active' ? 1 : ($b->status === 'pending' ? 2 : 3);
            
            if ($this->sortField === 'status') {
                if ($this->sortDirection === 'desc') {
                    return $statusPriorityB <=> $statusPriorityA;
                }
                return $statusPriorityA <=> $statusPriorityB;
            }
            
            // For other fields, first sort by status priority, then by the field
            if ($statusPriorityA !== $statusPriorityB && $this->sortField !== 'key' && $this->sortField !== 'title') {
                return $statusPriorityA <=> $statusPriorityB;
            }
            
            $valueA = $a->{$this->sortField} ?? '';
            $valueB = $b->{$this->sortField} ?? '';
            
            if ($this->sortDirection === 'desc') {
                return $valueB <=> $valueA;
            }
            return $valueA <=> $valueB;
        });
    }

    private function sortProjectsByWeek($projects)
    {
        $weekIndex = (int) str_replace('week_', '', $this->weekSortField);
        $startDate = now()->startOfWeek();
        $weekStart = $startDate->copy()->addWeeks($weekIndex);
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        return $projects->sort(function ($a, $b) use ($weekStart, $weekEnd) {
            $amountA = $a->transactions()
                ->where('status', 'pending')
                ->whereBetween('due_date', [$weekStart, $weekEnd])
                ->sum('amount');
                
            $amountB = $b->transactions()
                ->where('status', 'pending')
                ->whereBetween('due_date', [$weekStart, $weekEnd])
                ->sum('amount');
            
            if ($this->weekSortDirection === 'desc') {
                return $amountB <=> $amountA;
            }
            return $amountA <=> $amountB;
        });
    }

    public function sortBy($field)
    {
        // Reset week sorting when sorting by regular fields
        $this->weekSortField = null;
        
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function sortByWeek($weekField)
    {
        if ($this->weekSortField === $weekField) {
            $this->weekSortDirection = $this->weekSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->weekSortField = $weekField;
            $this->weekSortDirection = 'desc'; // Default to highest amounts first
        }
        
        // Reset regular field sorting
        $this->sortField = null;
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
