<?php

namespace App\Filament\Resources\CashflowResource\Pages;

use App\Filament\Resources\CashflowResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\ViewEntry;
use App\Models\ProjectTransaction;
use Illuminate\Support\Facades\DB;

class ViewCashflow extends ViewRecord
{
    protected static string $resource = CashflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->hidden(), // Hide edit since this is read-only
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Project Overview')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('key')
                                    ->label('Project Key'),
                                TextEntry::make('title')
                                    ->label('Project Title'),
                                TextEntry::make('developer.name')
                                    ->label('Developer'),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'success',
                                        'completed' => 'info',
                                        'exited' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('total_contract_value')
                                    ->label('Contract Value')
                                    ->money('USD'),
                                TextEntry::make('entry_date')
                                    ->label('Entry Date')
                                    ->date(),
                                TextEntry::make('exit_date')
                                    ->label('Exit Date')
                                    ->date(),
                            ]),
                    ]),

                Section::make('Cashflow Summary')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_revenue')
                                    ->label('Total Revenue')
                                    ->money('USD')
                                    ->color('success'),
                                TextEntry::make('total_expenses')
                                    ->label('Total Expenses')
                                    ->money('USD')
                                    ->color('danger'),
                                TextEntry::make('net_cashflow')
                                    ->label('Net Cashflow')
                                    ->money('USD')
                                    ->color(fn ($record) => $record->net_cashflow >= 0 ? 'success' : 'danger'),
                                TextEntry::make('unpaid_installments')
                                    ->label('Unpaid Installments')
                                    ->money('USD')
                                    ->color('warning'),
                            ]),
                    ]),

                Tabs::make('Details')
                    ->tabs([
                        Tabs\Tab::make('Monthly Breakdown')
                            ->schema([
                                ViewEntry::make('monthly_breakdown')
                                    ->view('filament.resources.cashflow.monthly-breakdown')
                                    ->viewData(fn ($record) => [
                                        'monthlyData' => $this->getMonthlyBreakdown($record),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Installment Schedule')
                            ->schema([
                                ViewEntry::make('installment_schedule')
                                    ->view('filament.resources.cashflow.installment-schedule')
                                    ->viewData(fn ($record) => [
                                        'installments' => $this->getInstallmentSchedule($record),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Transaction History')
                            ->schema([
                                ViewEntry::make('transaction_history')
                                    ->view('filament.resources.cashflow.transaction-history')
                                    ->viewData(fn ($record) => [
                                        'transactions' => $this->getTransactionHistory($record),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected function getMonthlyBreakdown($record): array
    {
        return DB::table('project_transactions')
            ->where('project_key', $record->key)
            ->where('status', 'done')
            ->selectRaw('
                DATE_FORMAT(transaction_date, "%Y-%m") as month,
                DATE_FORMAT(transaction_date, "%M %Y") as month_label,
                SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) as revenue,
                SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END) as expenses,
                (SUM(CASE WHEN financial_type = "revenue" THEN amount ELSE 0 END) - 
                 SUM(CASE WHEN financial_type = "expense" THEN amount ELSE 0 END)) as net
            ')
            ->groupBy('month', 'month_label')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->toArray();
    }

    protected function getInstallmentSchedule($record): array
    {
        return ProjectTransaction::where('project_key', $record->key)
            ->where('status', 'pending')
            ->where('transaction_date', '>', now())
            ->orderBy('transaction_date')
            ->select(['transaction_date', 'amount', 'financial_type', 'note'])
            ->get()
            ->toArray();
    }

    protected function getTransactionHistory($record): array
    {
        return ProjectTransaction::where('project_key', $record->key)
            ->orderBy('transaction_date', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }
}
