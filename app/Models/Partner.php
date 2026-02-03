<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Partner extends Model
{
    use LogsActivity;

    protected $fillable = [
        'company_id',
        'user_id',
        'is_customer',
        'is_supplier',
        'document_type',
        'document_number',
        'business_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'photo_url',
        'address',
        'district',
        'province',
        'department',
        'birth_date',
        'gender',
        'blood_type',
        'medical_notes',
        'allergies',
        'payment_terms',
        'credit_limit',
        'tax_id',
        'business_license',
        'provider_category',
        'status',
        'notes',
    ];

    protected $casts = [
        'is_customer' => 'boolean',
        'is_supplier' => 'boolean',
        'birth_date' => 'date',
        'credit_limit' => 'decimal:2',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('is_customer', true);
    }

    public function scopeSuppliers(Builder $query): Builder
    {
        return $query->where('is_supplier', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    public function getFullNameAttribute(): string
    {
        if ($this->business_name) {
            return $this->business_name;
        }

        return trim("{$this->first_name} {$this->last_name}");
    }

    // ========================================
    // ACTIVITY LOG
    // ========================================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
