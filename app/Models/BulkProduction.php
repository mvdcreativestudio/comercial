<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'formula_id',
        'quantity_produced',
        'batch_id',
        'production_date',
        'quantity_used'
    ];

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class, 'formula_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Formula::class, 'batch_id');
    }


    public function Product(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function bulkProductionBatches(): HasMany
    {
        return $this->hasMany(BulkProductionBatch::class);
    }

    public function packaging(): HasMany
    {
        return $this->hasMany(Packaging::class);
    }
}
