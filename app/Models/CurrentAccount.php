<?php

namespace App\Models;

use App\Enums\CurrentAccounts\StatusPaymentEnum;
use App\Enums\CurrentAccounts\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrentAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voucher',
        'total_debit',
        'transaction_date',
        'due_date',
        'status',
        'transaction_type',
        'client_id',
        'supplier_id',
        'currency_id',
        'current_account_settings_id',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'due_date' => 'datetime',
        'status' => StatusPaymentEnum::class,
        'transaction_type' => TransactionTypeEnum::class,
    ];

    protected $appends = ['payment_amount'];

    // generate attribute payment_amount
    public function getPaymentAmountAttribute()
    {
        return $this->payments->sum('payment_amount');
    }

    /**
     * Obtiene los pagos parciales asociados a esta cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(CurrentAccountPayment::class);
    }

    /**
     * Obtiene la moneda asociada a esta cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Obtiene el cliente asociado a esta cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Obtiene el proveedor asociado a esta cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene la configuración de la cuenta corriente asociada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function settings()
    {
        return $this->belongsTo(CurrentAccountSettings::class);
    }
}
