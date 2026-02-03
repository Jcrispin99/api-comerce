<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Journal extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'type',
        'is_fiscal',
        'document_type_code',
        'sequence_id',
        'company_id',
    ];

    protected $casts = [
        'is_fiscal' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journal) {
            if (!$journal->sequence_id) {
                $sequence = Sequence::create([
                    'sequence_size' => 8,
                    'step'          => 1,
                    'next_number'   => 0, // Comenzando en 0 como solicitaste
                ]);
                $journal->sequence_id = $sequence->id;
            }
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship with Purchase (assuming Purchase model will exist)
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
