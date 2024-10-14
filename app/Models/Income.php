<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'income_name',
        'income_description',
        'income_date',
        'income_amount',
        'payment_method_id',
        'income_category_id',
        'client_id',
        'supplier_id',
    ];

    protected $casts = [
        'income_date' => 'datetime',
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // Relación con IncomeCategory
    public function incomeCategory()
    {
        return $this->belongsTo(IncomeCategory::class);
    }

    // Relación con Client (opcional)
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relación con Supplier (opcional)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
