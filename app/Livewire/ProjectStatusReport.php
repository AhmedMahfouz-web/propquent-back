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

    // Sorting properties
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    // Additional filters
    public $minArea = '';
    public $maxArea = '';
    public $minContractValue = '';
    public $maxContractValue = '';
    public $contractDateFrom = '';
    public $contractDateTo = '';
    public $reservationDateFrom = '';
    public $reservationDateTo = '';
    public $minExpenses = '';
    public $maxExpenses = '';
    public $minRevenues = '';
    public $maxRevenues = '';
    public $compound = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'stage' => ['except' => ''],
        'type' => ['except' => ''],
        'investment_type' => ['except' => ''],
        'perPage' => ['except' => 25],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'minArea' => ['except' => ''],
        'maxArea' => ['except' => ''],
        'minContractValue' => ['except' => ''],
        'maxContractValue' => ['except' => ''],
        'contractDateFrom' => ['except' => ''],
        'contractDateTo' => ['except' => ''],
        'reservationDateFrom' => ['except' => ''],
        'reservationDateTo' => ['except' => ''],
        'minExpenses' => ['except' => ''],
        'maxExpenses' => ['except' => ''],
        'minRevenues' => ['except' => ''],
        'maxRevenues' => ['except' => ''],
        'compound' => ['except' => ''],
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

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    // Additional filter update methods
    public function updatedMinArea() { $this->resetPage(); }
    public function updatedMaxArea() { $this->resetPage(); }
    public function updatedMinContractValue() { $this->resetPage(); }
    public function updatedMaxContractValue() { $this->resetPage(); }
    public function updatedContractDateFrom() { $this->resetPage(); }
    public function updatedContractDateTo() { $this->resetPage(); }
    public function updatedReservationDateFrom() { $this->resetPage(); }
    public function updatedReservationDateTo() { $this->resetPage(); }
    public function updatedMinExpenses() { $this->resetPage(); }
    public function updatedMaxExpenses() { $this->resetPage(); }
    public function updatedMinRevenues() { $this->resetPage(); }
    public function updatedMaxRevenues() { $this->resetPage(); }
    public function updatedCompound() { $this->resetPage(); }

    public function clearFilters()
    {
        $this->reset([
            'search', 'status', 'stage', 'type', 'investment_type',
            'minArea', 'maxArea', 'minContractValue', 'maxContractValue',
            'contractDateFrom', 'contractDateTo', 'reservationDateFrom', 'reservationDateTo',
            'minExpenses', 'maxExpenses', 'minRevenues', 'maxRevenues', 'compound'
        ]);
        $this->resetPage();
    }

    #[Computed]
    public function projects()
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $query = Project::query()
            ->with(['transactions', 'valueCorrections', 'developer', 'compound'])
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
            ->when($this->compound, fn($q) => $q->whereHas('compound', function($query) {
                $query->where('name', 'like', '%' . $this->compound . '%');
            }))
            // Area filters
            ->when($this->minArea, fn($q) => $q->where('area', '>=', $this->minArea))
            ->when($this->maxArea, fn($q) => $q->where('area', '<=', $this->maxArea))
            // Contract value filters
            ->when($this->minContractValue, fn($q) => $q->where('total_contract_value', '>=', $this->minContractValue))
            ->when($this->maxContractValue, fn($q) => $q->where('total_contract_value', '<=', $this->maxContractValue))
            // Date filters
            ->when($this->contractDateFrom, fn($q) => $q->where('contract_date', '>=', $this->contractDateFrom))
            ->when($this->contractDateTo, fn($q) => $q->where('contract_date', '<=', $this->contractDateTo))
            ->when($this->reservationDateFrom, fn($q) => $q->where('reservation_date', '>=', $this->reservationDateFrom))
            ->when($this->reservationDateTo, fn($q) => $q->where('reservation_date', '<=', $this->reservationDateTo));

        // Apply sorting
        $this->applySorting($query);

        return $query->paginate($this->perPage);
    }

    private function applySorting($query)
    {
        switch ($this->sortBy) {
            case 'title':
                $query->orderBy('title', $this->sortDirection);
                break;
            case 'key':
                $query->orderBy('key', $this->sortDirection);
                break;
            case 'status':
                $query->orderBy('status', $this->sortDirection);
                break;
            case 'stage':
                $query->orderBy('stage', $this->sortDirection);
                break;
            case 'area':
                $query->orderBy('area', $this->sortDirection);
                break;
            case 'garden_area':
                $query->orderBy('garden_area', $this->sortDirection);
                break;
            case 'total_contract_value':
                $query->orderBy('total_contract_value', $this->sortDirection);
                break;
            case 'years_of_installment':
                $query->orderBy('years_of_installment', $this->sortDirection);
                break;
            case 'contract_date':
                $query->orderBy('contract_date', $this->sortDirection);
                break;
            case 'reservation_date':
                $query->orderBy('reservation_date', $this->sortDirection);
                break;
            case 'compound':
                $query->leftJoin('compounds', 'projects.compound_id', '=', 'compounds.id')
                     ->orderBy('compounds.name', $this->sortDirection)
                     ->select('projects.*');
                break;
            case 'total_expenses':
            case 'total_revenues':
            case 'net_profit':
                // For financial sorting, we'll need to calculate these values in the query
                $this->applySortingWithCalculatedFields($query);
                break;
            default:
                $query->orderBy('created_at', $this->sortDirection);
                break;
        }
    }

    private function applySortingWithCalculatedFields($query)
    {
        // For complex sorting based on calculated fields, we'll sort in PHP after fetching
        // This is less efficient but more accurate for complex calculations
        $query->orderBy('created_at', 'desc');
    }

    #[Computed]
    public function projectsData()
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $data = [];
        $projects = $this->projects;

        // If sorting by calculated fields, we need to sort the collection after calculations
        if (in_array($this->sortBy, ['total_expenses', 'total_revenues', 'net_profit'])) {
            $projectsWithData = [];
            
            foreach ($projects as $project) {
                $projectData = $this->calculateProjectData($project);
                $data[$project->key] = $projectData;
                $projectsWithData[] = [
                    'project' => $project,
                    'data' => $projectData
                ];
            }

            // Sort by the calculated field
            usort($projectsWithData, function($a, $b) {
                $aValue = $a['data'][$this->sortBy] ?? 0;
                $bValue = $b['data'][$this->sortBy] ?? 0;
                
                if ($this->sortBy === 'net_profit') {
                    $aValue = $a['data']['total_revenues'] - $a['data']['total_expenses'];
                    $bValue = $b['data']['total_revenues'] - $b['data']['total_expenses'];
                }
                
                return $this->sortDirection === 'asc' ? $aValue <=> $bValue : $bValue <=> $aValue;
            });

            // Update the projects collection order (this is a bit of a hack for pagination)
            $sortedProjects = collect($projectsWithData)->pluck('project');
            $projects->setCollection($sortedProjects);
        } else {
            foreach ($projects as $project) {
                $projectData = $this->calculateProjectData($project);
                $data[$project->key] = $projectData;
            }
        }

        // Apply financial filters after calculation
        if ($this->minExpenses || $this->maxExpenses || $this->minRevenues || $this->maxRevenues) {
            $filteredData = [];
            foreach ($data as $key => $projectData) {
                $include = true;
                
                if ($this->minExpenses && $projectData['total_expenses'] < $this->minExpenses) {
                    $include = false;
                }
                if ($this->maxExpenses && $projectData['total_expenses'] > $this->maxExpenses) {
                    $include = false;
                }
                if ($this->minRevenues && $projectData['total_revenues'] < $this->minRevenues) {
                    $include = false;
                }
                if ($this->maxRevenues && $projectData['total_revenues'] > $this->maxRevenues) {
                    $include = false;
                }
                
                if ($include) {
                    $filteredData[$key] = $projectData;
                }
            }
            $data = $filteredData;
        }

        return $data;
    }

    #[Computed]
    public function availableCompounds()
    {
        return \App\Models\Compound::orderBy('name')->pluck('name', 'name')->toArray();
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
