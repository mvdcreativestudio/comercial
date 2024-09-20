<?php

namespace App\Models;

use App\Enums\CurrentAccounts\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrentAccountSettings extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_type',
        'late_fee',
        'payment_terms',
    ];

    protected $casts = [
        'transaction_type' => TransactionTypeEnum::class,
    ];

    /**
     * Obtiene las cuentas corrientes asociadas a esta configuración.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function currentAccounts()
    {
        return $this->hasMany(CurrentAccount::class);
    }
}
