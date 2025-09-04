<?php

namespace App\Filament\Resources\CashflowResource\Pages;

use App\Filament\Resources\CashflowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ListCashflows extends ListRecords
{
    protected static string $resource = CashflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh_cache')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    // Clear cashflow related cache
                    cache()->forget('company_cashflow_summary');
                    cache()->forget('monthly_cashflow_data_6');
                    cache()->forget('monthly_cashflow_data_12');
                    cache()->forget('monthly_cashflow_data_24');
                    cache()->forget('monthly_cashflow_data_36');
                    
                    // Clear any other cashflow cache keys
                    $cacheKeys = ['company_cashflow_summary', 'monthly_cashflow_data_6', 'monthly_cashflow_data_12', 'monthly_cashflow_data_24', 'monthly_cashflow_data_36'];
                    foreach ($cacheKeys as $key) {
                        cache()->forget($key);
                    }
                    
                    $this->notify('success', 'Cashflow data refreshed successfully!');
                    return redirect()->to(request()->url());
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Projects'),
            'active' => Tab::make('Active Projects')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')),
            'profitable' => Tab::make('Profitable Projects')
                ->modifyQueryUsing(fn (Builder $query) => $query->havingRaw('net_cashflow > 0')),
            'loss_making' => Tab::make('Loss Making')
                ->modifyQueryUsing(fn (Builder $query) => $query->havingRaw('net_cashflow < 0')),
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
