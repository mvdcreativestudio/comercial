<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePosOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date' => 'required|date',
            'hour' => ['required', 'regex:/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/'], 
            'cash_register_log_id' => 'required|int',
            'cash_sales' => 'required|int',
            'pos_sales' => 'required|int',
            'discount' => 'required|int',
            'client_id' => 'nullable|int',
            'client_type' => 'required|string',
            'products' => 'required',
            'subtotal' => 'required|int',
            'total' => 'required|int',
            'notes' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'El fecha de la orden es obligatoria',
            'hour.required' => 'El hora de la orden es obligatoria',
            'cash_register_log_id.required' => 'El identificador del LOG de la caja registradora es obligatorio',
            'cash_sales.required' => 'La cantidad de dinero en efectivo es obligatoria.',
            'pos_sales.required' => 'La cantidad de dinero del POS es obligatoria.',
            'discount.required' => 'La cantidad de descuento es obligatoria.',
            'client_type.required' => 'El tipo de cliente es obligatorio.',
            'products.required' => 'Los productos de la orden son obligatorios.',
            'subtotal.required' => 'Es necesario el subtotal de la orden.',
            'total.required' => 'Es necesario el total de la orden.'
        ];
    }
}
