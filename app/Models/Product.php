<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'type',
        'image',
        'status',
        'draft',
        'is_trash',
        'category_id',
        'bulk_production_id'
      ];


    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

      

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function packaging(): HasMany
    {
        return $this->hasMany(Packaging::class, 'final_product_id');
    }

    public function BulkProduction(): HasMany
    {
        return $this->hasMany(BulkProduction::class, 'bulk_production_id');
    }

    /**
      * Obtiene las ordenes de compra asociadas al producto.
      *
      * @return BelongsToMany
    */
    public function orders(): BelongsToMany
    {
    return $this->belongsToMany(Order::class, 'order_products')
                ->withPivot('quantity', 'price')
                ->withTimestamps();
    }

    /**
     * Obtiene las recetas asociadas al producto.
     *
     * @return HasMany
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Obtiene las elaboraciones asociadas al producto.
     *
     * @return HasMany
    */
    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }
    
    public function batch(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
