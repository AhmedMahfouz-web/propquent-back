<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\ValueCorrection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class ProjectStatusReport extends Component
{
    use WithPagination;

    public $perPage = 25;
    public $search = '';
    public $status = '';
    public $stage = '';
    public $type = '';
    public $investment_type = '';
    public bool $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'stage' => ['except' => ''],
        'type' => ['except' => ''],
        'investment_type' => ['except' => ''],
        'perPage' => ['except' => 25],
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedStage()
    {
        $this->resetPage();
    }

    public function updatedType()
    {
        $this->resetPage();
    }

    public function updatedInvestmentType()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    #[Computed]
    public function projects()
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $query = Project::query()
            ->with(['transactions', 'valueCorrections', 'developer'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('key', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->stage, fn($q) => $q->where('stage', $this->stage))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->investment_type, fn($q) => $q->where('investment_type', $this->investment_type))
            ->orderBy('created_at', 'desc');

        return $query->paginate($this->perPage);
    }

    #[Computed]
    public function projectsData()
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $data = [];

        foreach ($this->projects as $project) {
            $projectData = $this->calculateProjectData($project);
            $data[$project->key] = $projectData;
        }

        return $data;
    }

    private function calculateProjectData(Project $project)
    {
        // Get all transactions for this project
        $transactions = $project->transactions;

        // Initialize data structure
        $data = [
            'project' => $project,
            'total_expenses' => 0,
            'total_revenues' => 0,
            'expense_breakdown' => [],
            'revenue_breakdown' => [],
            'asset_evaluation' => 0,
            'asset_correction' => 0,
            'entry_date' => null,
            'exit_date' => null,
        ];

        // Process transactions
        foreach ($transactions as $transaction) {
            $amount = (float) $transaction->amount;
            $serving = $transaction->serving ?? 'operation';
            $category = $transaction->transaction_category ?? 'general';

            // Determine if it's revenue or expense based on context
            // Since financial_type was dropped, we'll use serving and amount patterns
            $isRevenue = $this->determineTransactionType($transaction);

            if ($isRevenue) {
                $data['total_revenues'] += $amount;
                $key = $serving . '_' . $category;
                $data['revenue_breakdown'][$key] = ($data['revenue_breakdown'][$key] ?? 0) + $amount;
            } else {
                $data['total_expenses'] += $amount;
                $key = $serving . '_' . $category;
                $data['expense_breakdown'][$key] = ($data['expense_breakdown'][$key] ?? 0) + $amount;
            }
        }

        // Calculate entry and exit dates
        if ($transactions->isNotEmpty()) {
            $data['entry_date'] = $transactions->min('transaction_date');

            // Exit date only if status is "exited"
            if ($project->status === Project::STATUS_EXITED) {
                $data['exit_date'] = $transactions->max('transaction_date');
            }
        }

        // Get asset evaluation and correction
        $data['asset_evaluation'] = $this->calculateAssetEvaluation($project);
        $data['asset_correction'] = $this->calculateAssetCorrection($project);

        return $data;
    }

    private function determineTransactionType($transaction)
    {
        // Logic to determine if transaction is revenue or expense based on business rules

        // Check transaction category patterns for revenue
        $revenueCategories = ['rental', 'sales', 'income', 'profit', 'return', 'dividend', 'interest', 'commission'];
        $expenseCategories = ['maintenance', 'administrative', 'purchase', 'fee', 'cost', 'repair', 'tax', 'insurance', 'management'];

        $category = strtolower($transaction->transaction_category ?? '');
        $note = strtolower($transaction->note ?? '');

        // First check explicit category matches
        foreach ($revenueCategories as $revCat) {
            if (strpos($category, $revCat) !== false || strpos($note, $revCat) !== false) {
                return true;
            }
        }

        foreach ($expenseCategories as $expCat) {
            if (strpos($category, $expCat) !== false || strpos($note, $expCat) !== false) {
                return false;
            }
        }

        // Business logic based on serving type and context
        if ($transaction->serving === 'operation') {
            // Operation transactions are typically revenue (rental income, sales, etc.)
            return true;
        } elseif ($transaction->serving === 'asset') {
            // Asset transactions are typically expenses (property purchase, improvements, etc.)
            return false;
        }

        // Default to expense if uncertain
        return false;
    }

    private function calculateAssetEvaluation($project)
    {
        // Asset Evaluation = Total Asset Expenses - Total Asset Revenues + Asset Correction
        $assetExpenses = 0;
        $assetRevenues = 0;

        // Calculate asset expenses and revenues using the same logic as the main calculation
        foreach ($project->transactions()->where('serving', 'asset')->get() as $transaction) {
            $amount = (float) $transaction->amount;

            if ($this->determineTransactionType($transaction)) {
                $assetRevenues += $amount;
            } else {
                $assetExpenses += $amount;
            }
        }

        $assetCorrection = $this->calculateAssetCorrection($project);

        return $assetExpenses - $assetRevenues + $assetCorrection;
    }

    private function calculateAssetCorrection($project)
    {
        return $project->valueCorrections()->sum('correction_amount') ?? 0;
    }

    public function render()
    {
        return view('livewire.project-status-report');
    }
}
