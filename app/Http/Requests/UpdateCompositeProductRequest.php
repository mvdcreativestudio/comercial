<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompositeProductRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Cambia esto según la lógica de autorización si es necesario.
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:stores,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'product_ids' => ['required', 'array'], // IDs de los productos incluidos
            'product_ids.*' => ['exists:products,id'], // Verifica que cada ID de producto exista
        ];
    }

    /**
     * Mensajes de validación personalizados.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'store_id.required' => 'La tienda es obligatoria.',
            'title.required' => 'El título es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'product_ids.required' => 'Debes seleccionar al menos un producto.',
            'product_ids.*.exists' => 'Algunos productos no existen.',
        ];
    }
}
