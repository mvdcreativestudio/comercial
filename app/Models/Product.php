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
        'stock',
        'safety_margin',
        'bar_code',
        'build_price'
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
      * Obtiene los variaciones asociados al producto.
      *
      * @return BelongsToMany
    */
    public function flavors(): BelongsToMany
    {
        return $this->belongsToMany(Flavor::class, 'product_flavor')->withTimestamps();
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

    /**
     * Obtiene los filtros para la exportación de productos.
     *
     * @param $query
     * @param $filters
     * @return mixed
     */
    public function scopeFilterData($query, $filters)
    {
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Agrega más filtros según sea necesario
        return $query;
    }

    /**
     * Setea el precio del producto.
     * @param float|null $value
     * @return void
    */
    public function setPriceAttribute(?float $value): void
    {
        $this->attributes['price'] = $value !== null ? (float) $value : null;
    }

    /**
     * Setea el precio anterior del producto.
     * @param float $value
     * @return void
    */
    public function setOldPriceAttribute(float $value): void
    {
        $this->attributes['old_price'] = round($value, 2);
    }

    /**
     * Setea el descuento del producto.
     * @param float $value
     * @return void
    */
    public function setDiscountAttribute(float $value): void
    {
        $this->attributes['discount'] = round($value, 2);
    }

    /**
     * Obtiene el precio del producto.
     * @param float|null $value
     * @return float
    */
    public function priceLists()
    {
        return $this->belongsToMany(PriceList::class, 'price_list_products')
                    ->withPivot('price')
                    ->withTimestamps();
    }
    
    public function batch(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
