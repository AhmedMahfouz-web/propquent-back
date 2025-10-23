<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyProjectEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_key',
        'month_date',
        'asset_evaluation',
        'expense_asset',
        'revenue_asset',
        'value_correction',
        'previous_evaluation',
        'is_after_exit',
        'expense_operation',
        'revenue_operation',
        'profit_operation',
        'profit_asset_cumulative',
        'profit_operation_cumulative',
        'total_profit_cumulative',
        'expense_total',
        'revenue_total',
    ];

    protected $casts = [
        'month_date' => 'date',
        'asset_evaluation' => 'decimal:2',
        'expense_asset' => 'decimal:2',
        'revenue_asset' => 'decimal:2',
        'value_correction' => 'decimal:2',
        'previous_evaluation' => 'decimal:2',
        'is_after_exit' => 'boolean',
        'expense_operation' => 'decimal:2',
        'revenue_operation' => 'decimal:2',
        'profit_operation' => 'decimal:2',
        'profit_asset_cumulative' => 'decimal:2',
        'profit_operation_cumulative' => 'decimal:2',
        'total_profit_cumulative' => 'decimal:2',
        'expense_total' => 'decimal:2',
        'revenue_total' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_key', 'key');
    }

    /**
     * Get asset evaluation for a specific project and month
     */
    public static function getAssetEvaluation(string $projectKey, string $month): float
    {
        $evaluation = self::where('project_key', $projectKey)
            ->where('month_date', $month)
            ->first();

        return $evaluation ? (float) $evaluation->asset_evaluation : 0;
    }

    /**
     * Get company total asset evaluation for a specific month
     */
    public static function getCompanyAssetEvaluation(string $month): float
    {
        return self::where('month_date', $month)
            ->sum('asset_evaluation');
    }

    /**
     * Get asset evaluations for multiple months for a project
     */
    public static function getProjectEvaluations(string $projectKey, array $months): array
    {
        $evaluations = self::where('project_key', $projectKey)
            ->whereIn('month_date', $months)
            ->pluck('asset_evaluation', 'month_date')
            ->toArray();

        // Fill missing months with 0
        $result = [];
        foreach ($months as $month) {
            $result[$month] = $evaluations[$month] ?? 0;
        }

        return $result;
    }

    /**
     * Get company asset evaluations for multiple months
     */
    public static function getCompanyEvaluations(array $months): array
    {
        $evaluations = self::whereIn('month_date', $months)
            ->selectRaw('month_date, SUM(asset_evaluation) as total_evaluation')
            ->groupBy('month_date')
            ->pluck('total_evaluation', 'month_date')
            ->toArray();

        // Fill missing months with 0
        $result = [];
        foreach ($months as $month) {
            $result[$month] = (float) ($evaluations[$month] ?? 0);
        }

        return $result;
    }

    /**
     * Get the latest month's asset evaluation for a specific project
     */
    public static function getLatestAssetEvaluation(string $projectKey): float
    {
        $evaluation = self::where('project_key', $projectKey)
            ->orderBy('month_date', 'desc')
            ->first();

        return $evaluation ? (float) $evaluation->asset_evaluation : 0;
    }
}
