<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrentAccountSupplierRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convertimos los campos booleanos de texto a booleanos reales
        $this->merge([
            'is_paid' => filter_var($this->is_paid, FILTER_VALIDATE_BOOLEAN),
            'partial_payment' => filter_var($this->partial_payment, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

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
            'amount' => 'required|numeric|min:0',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'current_account_settings_id' => 'required|integer|exists:current_account_settings,id',
            'partial_payment' => 'required|boolean',
            'is_paid' => 'required|boolean',
            'amount_paid' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    // Si es un pago parcial, el monto pagado no puede ser mayor que el total
                    if ($this->partial_payment && $value > $this->amount) {
                        $fail('El monto pagado no puede ser mayor que el monto total.');
                    }

                    if ($this->partial_payment && $value == $this->amount) {
                        $fail('El monto parcial no puede ser igual al monto total.');
                    }
                },
            ],
            'payment_method_id' => [
                'nullable',
                'integer',
                'exists:payment_methods,id',
                function ($attribute, $value, $fail) {
                    // Si se ha indicado que hay pago parcial o la cuenta está pagada, debe seleccionarse un método de pago
                    if (($this->partial_payment || $this->is_paid) && empty($value)) {
                        $fail('Debe seleccionar un método de pago cuando se registra un pago parcial o total.');
                    }
                },
            ],
            'currency_id_current_account' => 'required|integer|exists:currencies,id',
            // 'currency_id_current_account_payment' => [
            //     'nullable',
            //     'integer',
            //     'exists:currencies,id',
            // ],
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
            'amount.required' => 'El monto total es obligatorio.',
            'amount.numeric' => 'El monto total debe ser un número.',
            'amount.min' => 'El monto total debe ser un valor positivo.',
            'supplier_id.required' => 'El proveedor es obligatorio.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido.',
            'current_account_settings_id.required' => 'El tipo de crédito es obligatorio.',
            'current_account_settings_id.exists' => 'El tipo de crédito seleccionado no es válido.',
            'partial_payment.required' => 'Debe especificar si es un pago parcial.',
            'partial_payment.boolean' => 'El valor de pago parcial debe ser verdadero o falso.',
            'is_paid.required' => 'Debe especificar si la cuenta está pagada.',
            'is_paid.boolean' => 'El valor de pagado debe ser verdadero o falso.',
            'amount_paid.numeric' => 'El monto pagado debe ser un número.',
            'amount_paid.min' => 'El monto pagado debe ser un valor positivo.',
            'payment_method_id.integer' => 'El método de pago debe ser un número entero.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido.',
            'currency_id_current_account.required' => 'Debe seleccionar una moneda para la cuenta corriente.',
            'currency_id_current_account.exists' => 'La moneda seleccionada no es válida.',
            // 'currency_id_current_account_payment.exists' => 'La moneda del pago parcial no es válida.',
        ];
    }
}
