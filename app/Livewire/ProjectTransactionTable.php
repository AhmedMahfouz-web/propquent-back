<?php

namespace App\Livewire;

use Livewire\Component;

class ProjectTransactionTable extends Component
{
    public function mount()
    {
        // Redirect to the Filament resource instead of showing custom table
        return redirect()->route('filament.admin.resources.project-transactions.index');
    }


    public function render()
    {
        return view('livewire.project-transaction-table');
    }
}
