<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoreRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Supongamos que solo los usuarios con un rol específico pueden crear tiendas.
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'string|max:255',
            'email' => 'required|email|unique:stores,email',
            'rut' => 'required|string|max:255|unique:stores,rut',
            'ecommerce' => 'required|boolean',
            'status' => 'required|boolean',
            'accepts_mercadopago' => 'required|boolean',
            'invoices_enabled' => 'boolean',
        ];

        if ($this->boolean('invoices_enabled')) {
            $rules['pymo_user'] = 'required|string|max:255';
            $rules['pymo_password'] = 'required|string|max:255';
            $rules['pymo_branch_office'] = 'required|string|max:255';
        }

        if ($this->boolean('accepts_mercadopago')) {
            $rules['mercadoPagoPublicKey'] = 'required|string|max:255';
            $rules['mercadoPagoAccessToken'] = 'required|string|max:255';
        }

        return $rules;
    }
}
