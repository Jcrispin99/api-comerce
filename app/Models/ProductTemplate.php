<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductTemplate extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productProducts(): HasMany
    {
        return $this->hasMany(ProductProduct::class);
    }

    /**
     * Alias for productProducts to match user's explanation
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductProduct::class);
    }

    /**
     * Accessor for SKU from the first variant
     */
    protected function sku(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->variants->first()?->sku,
        );
    }

    /**
     * Accessor for Barcode from the first variant
     */
    protected function barcode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->variants->first()?->barcode,
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'price', 'category_id', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
