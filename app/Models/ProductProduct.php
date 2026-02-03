<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductProduct extends Model
{
    use LogsActivity;

    protected $fillable = [
        'product_template_id',
        'sku',
        'barcode',
        'price',
        'cost_price',
        'is_principal',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_principal' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($productProduct) {
            if (empty($productProduct->barcode)) {
                $productProduct->barcode = static::generateUniqueBarcode();
            }
        });
    }

    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_value_product_product');
    }

    /**
     * Generates a unique EAN-13 barcode
     */
    public static function generateUniqueBarcode(): string
    {
        do {
            // Prefix 77 + 10 random digits
            $number = '77' . str_pad((string) mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            $barcode = $number . static::getEanChecksum($number);
        } while (static::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Calculate EAN-13 checksum
     */
    private static function getEanChecksum(string $number): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $number[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sku', 'barcode', 'price', 'cost_price', 'is_principal'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
