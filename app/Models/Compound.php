<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Developer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compound extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'developer_id'
    ];


    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }
}
