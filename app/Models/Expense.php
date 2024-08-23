<?php

namespace App\Models;

use App\Enums\Expense\ExpenseStatusEnum;
use App\Enums\Expense\ExpenseTemporalStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'amount',
        'status',
        'due_date',
        'temporal_status',
        'supplier_id',
        'expense_category_id',
        'store_id',
    ];

    protected $casts = [
        'status' => ExpenseStatusEnum::class,
    ];

    // Atributos adicionales
    protected $appends = ['total_payments'];

    public $timestamps = true;

    /**
     * Obtiene el proveedor asociado al gasto.
     *
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene la categoría asociada al gasto.
     *
     * @return BelongsTo
     */
    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    /**
     * Obtiene la tienda asociada al gasto.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Obtiene los pagos parciales asociados al gasto.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ExpensePaymentMethod::class);
    }

    /**
     * Obtiene el monto total de los pagos asociados al gasto.
     *
     * @return float
     */
    public function getTotalPaymentsAttribute(): float
    {
        return $this->payments()->sum('amount_paid');
    }

    /**
     * Obtiene la diferencia entre el monto del gasto y el monto total de los pagos asociados al gasto.
     *
     * @return float
     */
    public function getDifferenceAmountPaidAttribute(): float
    {
        return $this->amount - $this->total_payments;
    }

    /**
     * Calcula y obtiene el estado temporal del gasto basado en la fecha de vencimiento.
     *
     * @return string
     */
    public function calculateTemporalStatus(): string
    {
        $now = now();
        // si la fecha de vencimiento es mayor a la fecha actual
        if ($this->due_date > $now) {
            return ExpenseTemporalStatusEnum::ON_TIME->value;
        // si la fecha de vencimiento es mayor a la fecha actual menos 3 días
        } elseif ($this->due_date > $now->subDays(3)) {
            return ExpenseTemporalStatusEnum::DUE_SOON->value;
        // si la fecha de vencimiento es menor a la fecha actual
        } else {
            return ExpenseTemporalStatusEnum::OVERDUE->value;
        }
    }
}
