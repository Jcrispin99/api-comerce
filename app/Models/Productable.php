<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Productable extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_product_id',
        'productable_id',
        'productable_type',
        'quantity',
        'price',
        'subtotal',
        'tax_id',
        'tax_rate',
        'tax_amount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the parent model (Purchase, Sale, etc.)
     */
    public function productable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the product variant
     */
    public function productProduct(): BelongsTo
    {
        return $this->belongsTo(ProductProduct::class, 'product_product_id');
    }

    /**
     * Get the tax
     */
    // public function tax(): BelongsTo
    // {
    //     return $this->belongsTo(Tax::class);
    // }
}
