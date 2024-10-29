<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
      'name', 
      'phone', 
      'address', 
      'city', 
      'state', 
      'country', 
      'email', 
      'doc_type', 
      'doc_number', 
      'default_payment_method',
    ];

    /**
     * Obtiene las ordenes de compra asociadas al proveedor.
     *
     * @return HasMany
    */
    public function orders(): HasMany
    {
      return $this->hasMany(SupplierOrder::class);
    }

    public function purchaseOrder(): HasMany
    {
      return $this->hasMany(PurchaseOrder::class);
    }
}

