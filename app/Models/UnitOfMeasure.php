<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'family',
        'base_unit_id',
        'factor',
        'is_active',
    ];

    protected $casts = [
        'factor' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Get the base unit of measure (parent).
     */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'base_unit_id');
    }

    /**
     * Get the sub units of measure (children).
     */
    public function subUnits(): HasMany
    {
        return $this->hasMany(UnitOfMeasure::class, 'base_unit_id');
    }
}
