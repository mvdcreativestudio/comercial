<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    protected $fillable = ['name', 'description'];

    // Relación muchos a muchos con clientes
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'client_price_lists');
    }

    // Relación muchos a muchos con productos
    public function products()
    {
        return $this->belongsToMany(Product::class, 'price_list_products')
                    ->withPivot('price')
                    ->withTimestamps();
    }
}
