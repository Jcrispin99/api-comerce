<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Sequence extends Model
{
    use LogsActivity;

    protected $fillable = [
        'sequence_size',
        'step',
        'next_number',
    ];

    protected $casts = [
        'sequence_size' => 'integer',
        'step' => 'integer',
        'next_number' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
