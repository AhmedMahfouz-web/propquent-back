<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCache extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'report_cache';

    protected $fillable = [
        'report_type',
        'report_key',
        'report_data',
        'expires_at',
    ];

    protected $casts = [
        'report_data' => 'array',
        'expires_at' => 'datetime',
    ];
}
