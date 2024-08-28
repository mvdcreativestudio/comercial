<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'quantity',
        'production_date',
        'product_id',
        'raw_material_id',
        'purchase_order_items_id',
        'expiration_date',
    ];

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_items_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function purchaseEntry(): HasMany
    {
      return $this->hasMany(PurchaseEntry::class);
    }

    public function BulkProductionBatch(): HasMany
    {
      return $this->hasMany(BulkProductionBatch::class);
    }

    public function BulkProduction(): HasMany
    {
      return $this->hasMany(BulkProduction::class);
    }
}
