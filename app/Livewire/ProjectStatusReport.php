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

    // Column-specific filters (Excel-style)
    public $columnFilters = [
        'status' => [],
        'stage' => [],
        'type' => [],
        'investment_type' => [],
        'compound' => [],
        'unit_no' => [],
        'area_range' => ['min' => '', 'max' => ''],
        'garden_area_range' => ['min' => '', 'max' => ''],
        'contract_value_range' => ['min' => '', 'max' => ''],
        'years_range' => ['min' => '', 'max' => ''],
        'contract_date_range' => ['from' => '', 'to' => ''],
        'reservation_date_range' => ['from' => '', 'to' => ''],
        'expenses_range' => ['min' => '', 'max' => ''],
        'net_profit_range' => ['min' => '', 'max' => ''],
    ];

    // Track which column filters are open
    public $openFilterColumn = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 25],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'columnFilters' => ['except' => []],
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

    public function sortByColumn($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
        
        // Force refresh for calculated fields
        if (in_array($field, ['total_expenses', 'total_revenues', 'net_profit'])) {
            // Clear any cached data to force recalculation
            unset($this->cachedProjectsData);
        }
    }

    public function toggleColumnFilter($column)
    {
        $this->openFilterColumn = $this->openFilterColumn === $column ? null : $column;
    }

    public function closeColumnFilter()
    {
        $this->openFilterColumn = null;
    }

    public function updateColumnFilter($column, $value, $type = 'checkbox')
    {
        if ($type === 'checkbox') {
            if (in_array($value, $this->columnFilters[$column])) {
                $this->columnFilters[$column] = array_diff($this->columnFilters[$column], [$value]);
            } else {
                $this->columnFilters[$column][] = $value;
            }
        } elseif ($type === 'range') {
            $this->columnFilters[$column] = $value;
        }
        
        $this->resetPage();
    }

    public function clearColumnFilter($column)
    {
        if (isset($this->columnFilters[$column])) {
            if (is_array($this->columnFilters[$column]) && isset($this->columnFilters[$column]['min'])) {
                // Range filter
                $this->columnFilters[$column] = ['min' => '', 'max' => ''];
            } else {
                // Checkbox filter
                $this->columnFilters[$column] = [];
            }
        }
        $this->resetPage();
    }

    public function clearAllFilters()
    {
        $this->columnFilters = [
            'status' => [],
            'stage' => [],
            'type' => [],
            'investment_type' => [],
            'compound' => [],
            'unit_no' => [],
            'area_range' => ['min' => '', 'max' => ''],
            'garden_area_range' => ['min' => '', 'max' => ''],
            'contract_value_range' => ['min' => '', 'max' => ''],
            'years_range' => ['min' => '', 'max' => ''],
            'contract_date_range' => ['from' => '', 'to' => ''],
            'reservation_date_range' => ['from' => '', 'to' => ''],
            'expenses_range' => ['min' => '', 'max' => ''],
            'net_profit_range' => ['min' => '', 'max' => ''],
        ];
        $this->search = '';
        $this->resetPage();
    }

    public function updatedColumnFilters()
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
            ->with(['transactions', 'valueCorrections', 'developer', 'compound'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('key', 'like', '%' . $this->search . '%');
                });
            });

        // Apply column filters
        $this->applyColumnFilters($query);

        // Apply sorting
        $this->applySorting($query);

        return $query->paginate($this->perPage);
    }

    private function applyColumnFilters($query)
    {
        // Status filter
        if (!empty($this->columnFilters['status'])) {
            $query->whereIn('status', $this->columnFilters['status']);
        }

        // Stage filter
        if (!empty($this->columnFilters['stage'])) {
            $query->whereIn('stage', $this->columnFilters['stage']);
        }

        // Type filter
        if (!empty($this->columnFilters['type'])) {
            $query->whereIn('type', $this->columnFilters['type']);
        }

        // Investment type filter
        if (!empty($this->columnFilters['investment_type'])) {
            $query->whereIn('investment_type', $this->columnFilters['investment_type']);
        }

        // Compound filter
        if (!empty($this->columnFilters['compound'])) {
            $query->whereHas('compound', function($q) {
                $q->whereIn('name', $this->columnFilters['compound']);
            });
        }

        // Unit number filter
        if (!empty($this->columnFilters['unit_no'])) {
            $query->whereIn('unit_no', $this->columnFilters['unit_no']);
        }

        // Area range filter
        if (!empty($this->columnFilters['area_range']['min']) || !empty($this->columnFilters['area_range']['max'])) {
            if (!empty($this->columnFilters['area_range']['min'])) {
                $query->where('area', '>=', $this->columnFilters['area_range']['min']);
            }
            if (!empty($this->columnFilters['area_range']['max'])) {
                $query->where('area', '<=', $this->columnFilters['area_range']['max']);
            }
        }

        // Garden area range filter
        if (!empty($this->columnFilters['garden_area_range']['min']) || !empty($this->columnFilters['garden_area_range']['max'])) {
            if (!empty($this->columnFilters['garden_area_range']['min'])) {
                $query->where('garden_area', '>=', $this->columnFilters['garden_area_range']['min']);
            }
            if (!empty($this->columnFilters['garden_area_range']['max'])) {
                $query->where('garden_area', '<=', $this->columnFilters['garden_area_range']['max']);
            }
        }

        // Contract value range filter
        if (!empty($this->columnFilters['contract_value_range']['min']) || !empty($this->columnFilters['contract_value_range']['max'])) {
            if (!empty($this->columnFilters['contract_value_range']['min'])) {
                $query->where('total_contract_value', '>=', $this->columnFilters['contract_value_range']['min']);
            }
            if (!empty($this->columnFilters['contract_value_range']['max'])) {
                $query->where('total_contract_value', '<=', $this->columnFilters['contract_value_range']['max']);
            }
        }

        // Years range filter
        if (!empty($this->columnFilters['years_range']['min']) || !empty($this->columnFilters['years_range']['max'])) {
            if (!empty($this->columnFilters['years_range']['min'])) {
                $query->where('years_of_installment', '>=', $this->columnFilters['years_range']['min']);
            }
            if (!empty($this->columnFilters['years_range']['max'])) {
                $query->where('years_of_installment', '<=', $this->columnFilters['years_range']['max']);
            }
        }

        // Contract date range filter
        if (!empty($this->columnFilters['contract_date_range']['from']) || !empty($this->columnFilters['contract_date_range']['to'])) {
            if (!empty($this->columnFilters['contract_date_range']['from'])) {
                $query->where('contract_date', '>=', $this->columnFilters['contract_date_range']['from']);
            }
            if (!empty($this->columnFilters['contract_date_range']['to'])) {
                $query->where('contract_date', '<=', $this->columnFilters['contract_date_range']['to']);
            }
        }

        // Reservation date range filter
        if (!empty($this->columnFilters['reservation_date_range']['from']) || !empty($this->columnFilters['reservation_date_range']['to'])) {
            if (!empty($this->columnFilters['reservation_date_range']['from'])) {
                $query->where('reservation_date', '>=', $this->columnFilters['reservation_date_range']['from']);
            }
            if (!empty($this->columnFilters['reservation_date_range']['to'])) {
                $query->where('reservation_date', '<=', $this->columnFilters['reservation_date_range']['to']);
            }
        }
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

            // For calculated field sorting, we need to handle pagination differently
            // Get the sorted projects and re-paginate them
            $sortedProjects = collect($projectsWithData)->pluck('project');
            
            // Create a new paginated collection with the sorted projects
            $currentPage = request()->get('page', 1);
            $perPage = $this->perPage;
            $total = $sortedProjects->count();
            
            $paginatedProjects = $sortedProjects->forPage($currentPage, $perPage);
            
            // Create new paginator instance
            $projects = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedProjects,
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        } else {
            foreach ($projects as $project) {
                $projectData = $this->calculateProjectData($project);
                $data[$project->key] = $projectData;
            }
        }

        // Apply financial filters after calculation (for calculated fields)
        if (!empty($this->columnFilters['expenses_range']['min']) || !empty($this->columnFilters['expenses_range']['max']) ||
            !empty($this->columnFilters['net_profit_range']['min']) || !empty($this->columnFilters['net_profit_range']['max'])) {
            
            $filteredData = [];
            foreach ($data as $key => $projectData) {
                $include = true;
                
                // Expenses range filter
                if (!empty($this->columnFilters['expenses_range']['min']) && $projectData['total_expenses'] < $this->columnFilters['expenses_range']['min']) {
                    $include = false;
                }
                if (!empty($this->columnFilters['expenses_range']['max']) && $projectData['total_expenses'] > $this->columnFilters['expenses_range']['max']) {
                    $include = false;
                }
                
                // Net profit range filter
                $netProfit = $projectData['total_revenues'] - $projectData['total_expenses'];
                if (!empty($this->columnFilters['net_profit_range']['min']) && $netProfit < $this->columnFilters['net_profit_range']['min']) {
                    $include = false;
                }
                if (!empty($this->columnFilters['net_profit_range']['max']) && $netProfit > $this->columnFilters['net_profit_range']['max']) {
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

    #[Computed]
    public function getUniqueValues()
    {
        return [
            'status' => Project::distinct()->pluck('status')->filter()->sort()->values(),
            'stage' => Project::distinct()->pluck('stage')->filter()->sort()->values(),
            'type' => Project::distinct()->pluck('type')->filter()->sort()->values(),
            'investment_type' => Project::distinct()->pluck('investment_type')->filter()->sort()->values(),
            'compound' => \App\Models\Compound::orderBy('name')->pluck('name')->values(),
            'unit_no' => Project::distinct()->pluck('unit_no')->filter()->sort()->values(),
        ];
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

        // Ensure all required keys exist
        $data['entry_date'] = $data['entry_date'] ?? null;
        $data['exit_date'] = $data['exit_date'] ?? null;

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
