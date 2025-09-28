<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\UserTransaction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Livewire\Component;

class UserProfileModal extends Component implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    public User $user;
    public float $netDeposit = 0;
    public $lastTransactions;

    public function mount(User $user): void
    {
        $this->user = $user;

        $netDepositData = UserTransaction::where('user_id', $this->user->id)
            ->where('status', 'Done')
            ->selectRaw('SUM(CASE WHEN type = "Deposit" THEN amount ELSE 0 END) as total_deposits, SUM(CASE WHEN type = "Withdraw" THEN amount ELSE 0 END) as total_withdrawals')
            ->first();

        $this->netDeposit = ($netDepositData->total_deposits ?? 0) - ($netDepositData->total_withdrawals ?? 0);

        $this->lastTransactions = UserTransaction::where('user_id', $this->user->id)
            ->latest()
            ->limit(5)
            ->get();
    }

    public function userInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->user)
            ->schema([
                Section::make('User Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('full_name')->icon('heroicon-o-user-circle'),
                            TextEntry::make('email')->icon('heroicon-o-envelope'),
                        ]),
                    ]),
                Section::make('Financial Summary')
                    ->schema([
                        Grid::make(1)->schema([
                            TextEntry::make('net_deposit')
                                ->label('Net Deposit')
                                ->money('usd', true)
                                ->state($this->netDeposit)
                                ->icon($this->netDeposit >= 0 ? 'heroicon-o-arrow-up-circle' : 'heroicon-o-arrow-down-circle')
                                ->color($this->netDeposit >= 0 ? 'success' : 'danger'),
                        ])
                    ]),
                Section::make('Additional Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('phone_number')->icon('heroicon-o-phone'),
                            TextEntry::make('country')->icon('heroicon-o-flag'),
                            TextEntry::make('last_login_at')->dateTime()->icon('heroicon-o-clock'),
                            TextEntry::make('created_at')->label('Joined On')->date()->icon('heroicon-o-calendar-days'),
                        ]),
                    ])->columns(2),
                Section::make('Last 5 Transactions')
                    ->schema([
                        Grid::make(1)->schema([
                            ViewEntry::make('last_transactions')
                                ->label('')
                                ->view('infolists.components.last-transactions-list', ['transactions' => $this->lastTransactions])
                        ])
                    ])
            ]);
    }

    public function render()
    {
        return view('livewire.user-profile-modal');
    }
}
