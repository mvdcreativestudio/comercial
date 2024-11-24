<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        $store = $this->route('store');

        $rules = [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email'],
            'rut' => ['sometimes', 'string'],
            'ecommerce' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
            'accepts_mercadopago_online' => 'required|boolean',
            'accepts_mercadopago_presencial' => 'required|boolean',
            'invoices_enabled' => 'boolean',
            'accepts_peya_envios' => 'sometimes|boolean',
        ];

        if ($this->boolean('invoices_enabled')) {
            $rules += [
                'pymo_user' => 'required|string|max:255',
                'pymo_password' => 'required|string|max:255',
                'automatic_billing' => 'boolean',
            ];
        }


        if ($this->boolean('accepts_peya_envios')) {
            $rules += [
                'peya_envios_key' => 'required|string|max:255',
            ];
        }

        if ($this->boolean('accepts_mercadopago_online')) {
            $rules += [
                'mercadoPagoPublicKeyOnline' => 'required|string|max:255',
                'mercadoPagoAccessTokenOnline' => 'required|string|max:255',
                'mercadoPagoSecretKeyOnline' => 'required|string|max:255',
            ];
        }

        if ($this->boolean('accepts_mercadopago_presencial')) {
            $rules += [
                'mercadoPagoPublicKeyPresencial' => 'required|string|max:255',
                'mercadoPagoAccessTokenPresencial' => 'required|string|max:255',
                'mercadoPagoSecretKeyPresencial' => 'required|string|max:255',
                'mercadoPagoUserIdPresencial' => 'required|string|max:255',
                'street_number' => 'required|string|max:255',
                'street_name' => 'required|string|max:255',
                'city_name' => 'required|string|max:255',
                'state_name' => 'required|string|max:255',
                'latitude' => 'required|string|max:255',
                'longitude' => 'required|string|max:255',
                'reference' => 'nullable|string|max:255',
            ];
        }

        if ($this->boolean('scanntech')) {
            $rules += [
                'scanntechCompany' => 'required|string|max:255',
                'scanntechBranch' => 'required|string|max:255',
            ];
        }

        return $rules;
    }
}
