<?php

namespace App\Http\Requests;

use App\Enums\Expense\ExpenseStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            // 'status' => [
            //     'required',
            //     new Enum(ExpenseStatusEnum::class),
            // ],
            'due_date' => 'required|date',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'expense_category_id' => 'required|integer|exists:expense_categories,id',
            'store_id' => 'nullable|integer|exists:stores,id',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'El campo monto es requerido.',
            'amount.numeric' => 'El campo monto debe ser un número.',
            // 'status.required' => 'El campo estado es requerido.',
            // 'status.enum' => 'El campo estado no es válido.',
            'due_date.required' => 'El campo fecha de vencimiento es requerido.',
            'due_date.date' => 'El campo fecha de vencimiento debe ser una fecha válida.',
            'supplier_id.required' => 'El campo proveedor es requerido.',
            'supplier_id.integer' => 'El campo proveedor debe ser un número entero.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
            'expense_category_id.required' => 'El campo categoría de gasto es requerido.',
            'expense_category_id.integer' => 'El campo categoría de gasto debe ser un número entero.',
            'expense_category_id.exists' => 'La categoría de gasto seleccionada no es válida.',
            'store_id.integer' => 'El campo tienda debe ser un número entero.',
            'store_id.exists' => 'La tienda seleccionada no es válida.',
        ];
    }
}
