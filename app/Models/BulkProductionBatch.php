<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_production_id',
        'batch_id',
        'quantity_used'
    ];

    public function bulkProduction(): BelongsTo
    {
        return $this->belongsTo(BulkProduction::class, 'product_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'product_id');
    }
}
