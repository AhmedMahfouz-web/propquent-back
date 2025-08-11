<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_key',
        'evaluation_date',
        'evaluation_amount',
        'notes',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'evaluation_amount' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_key', 'key');
    }

    /**
     * Parse month string to evaluation date
     */
    private static function parseMonthToDate(string $month): string
    {
        // Handle different month formats
        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            // Format: 2025-07
            return $month . '-01';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $month)) {
            // Format: 2025-07-01 (already full date)
            return Carbon::parse($month)->startOfMonth()->format('Y-m-d');
        } else {
            // Try to parse with Carbon
            try {
                return Carbon::parse($month)->startOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback: assume Y-m format
                return Carbon::createFromFormat('Y-m', $month)->startOfMonth()->format('Y-m-d');
            }
        }
    }

    /**
     * Get evaluation for a specific project and month
     */
    public static function getEvaluationForMonth(string $projectKey, string $month): float
    {
        $evaluationDate = self::parseMonthToDate($month);

        $evaluation = self::where('project_key', $projectKey)
            ->whereDate('evaluation_date', $evaluationDate)
            ->first();

        return $evaluation ? (float) $evaluation->evaluation_amount : 0;
    }

    /**
     * Set evaluation for a specific project and month
     */
    public static function setEvaluationForMonth(string $projectKey, string $month, float $amount, ?string $notes = null): self
    {
        $evaluationDate = self::parseMonthToDate($month);

        return DB::transaction(function () use ($projectKey, $evaluationDate, $amount, $notes) {
            // First try to find existing record
            $existing = self::where('project_key', $projectKey)
                ->whereDate('evaluation_date', $evaluationDate)
                ->first();

            if ($existing) {
                // Update existing record
                $existing->update([
                    'evaluation_amount' => $amount,
                    'notes' => $notes,
                ]);
                return $existing;
            } else {
                // Create new record
                return self::create([
                    'project_key' => $projectKey,
                    'evaluation_date' => $evaluationDate,
                    'evaluation_amount' => $amount,
                    'notes' => $notes,
                ]);
            }
        });
    }
}
