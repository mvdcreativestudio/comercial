<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
    */
    protected $table = 'currency_rates';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
    */
    protected $fillable = [
        'name',
        'date',
        'buy',
        'sell'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
    */
    protected $casts = [
        'date' => 'date',
        'buy' => 'decimal:5',
        'sell' => 'decimal:5',
    ];
}
