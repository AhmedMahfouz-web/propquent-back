<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ValueCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_key',
        'correction_date',
        'correction_amount',
        'notes',
    ];

    protected $casts = [
        'correction_date' => 'date',
        'correction_amount' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_key', 'key');
    }

    /**
     * Parse month string to correction date
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
     * Get value correction for a specific project and month
     */
    public static function getCorrectionForMonth(string $projectKey, string $month): float
    {
        $correctionDate = self::parseMonthToDate($month);

        $correction = self::where('project_key', $projectKey)
            ->whereDate('correction_date', $correctionDate)
            ->first();

        return $correction ? (float) $correction->correction_amount : 0;
    }

    /**
     * Set value correction for a specific project and month
     */
    public static function setCorrectionForMonth(string $projectKey, string $month, float $amount, ?string $notes = null): self
    {
        $correctionDate = self::parseMonthToDate($month);

        return DB::transaction(function () use ($projectKey, $correctionDate, $amount, $notes) {
            // First try to find existing record
            $existing = self::where('project_key', $projectKey)
                ->whereDate('correction_date', $correctionDate)
                ->first();

            if ($existing) {
                // Update existing record
                $existing->update([
                    'correction_amount' => $amount,
                    'notes' => $notes,
                ]);
                return $existing;
            } else {
                // Create new record
                return self::create([
                    'project_key' => $projectKey,
                    'correction_date' => $correctionDate,
                    'correction_amount' => $amount,
                    'notes' => $notes,
                ]);
            }
        });
    }
}
