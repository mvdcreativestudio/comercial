<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
      'order_id', 'store_id', 'type', 'serie', 'nro',
      'caeNumber', 'caeRange', 'caeExpirationDate', 'total',
      'emitionDate', 'sentXmlHash', 'securityCode', 'qrUrl', 'cfeId'
    ];

    protected $casts = [
      'caeRange' => 'array',
      'caeExpirationDate' => 'date',
      'emitionDate' => 'datetime',
    ];

    /**
     * Obtiene la orden asociada al recibo.
     *
     * @return BelongsTo
    */
    public function order(): BelongsTo
    {
      return $this->belongsTo(Order::class);
    }

    /**
     * Obtiene la tienda asociada al recibo.
     *
     * @return BelongsTo
    */
    public function store(): BelongsTo
    {
      return $this->belongsTo(Store::class);
    }
}
