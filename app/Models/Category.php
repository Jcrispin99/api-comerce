<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Category extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'full_name',
        'parent_id',
        'is_active',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'full_name', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            $category->generateFullName();
        });

        static::updating(function (Category $category) {
            $category->generateFullName();
        });
    }

    public function generateFullName(): void
    {
        if ($this->parent_id) {
            $parent = Category::find($this->parent_id);
            if ($parent) {
                $this->full_name = "{$parent->name} / {$this->name}";
                return;
            }
        }

        $this->full_name = $this->name;
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        //return $this->hasMany(ProductTemplate::class);
    }
}
