<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Company extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_name',
        'trade_name',
        'ruc',
        'address',
        'phone',
        'email',
        'logo_url',
        'ubigeo',
        'urbanization',
        'department',
        'province',
        'district',
        'active',
        'parent_id',
        'branch_code',
        'is_main_office',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'business_name',
                'trade_name',
                'ruc',
                'address',
                'phone',
                'email',
                'branch_code',
                'active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'is_main_office' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include inactive companies.
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /**
     * Get the users for the company.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get all branches of this company (if this is a main office).
     */
    public function branches()
    {
        return $this->hasMany(Company::class, 'parent_id');
    }

    /**
     * Get the parent company (if this is a branch).
     */
    public function parent()
    {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    /**
     * Get the partners (customers/providers) for the company.
     */
    public function partners()
    {
        //return $this->hasMany(Partner::class);
    }

    /**
     * Scope a query to only include main offices (companies without parent).
     */
    public function scopeMainOffices($query)
    {
        return $query->whereNull('parent_id')->orWhere('is_main_office', true);
    }

    /**
     * Scope a query to only include branches.
     */
    public function scopeBranches($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Check if this company is a branch.
     */
    public function isBranch(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Check if this company is a main office.
     */
    public function isMainOffice(): bool
    {
        return is_null($this->parent_id) || $this->is_main_office;
    }

    /**
     * Get all branches including this one (if main office) or siblings (if branch).
     */
    public function getAllBranches()
    {
        if ($this->isMainOffice()) {
            return $this->branches;
        }

        return $this->parent?->branches ?? collect([]);
    }

    /**
     * Configure activity log options
     */
    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logOnly([
    //             'business_name',
    //             'trade_name',
    //             'ruc',
    //             'address',
    //             'phone',
    //             'email',
    //             'branch_code',
    //             'district',
    //             'active',
    //         ])
    //         ->logOnlyDirty()
    //         ->dontSubmitEmptyLogs();
    // }
}
