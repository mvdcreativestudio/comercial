<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:simple,configurable',
            'status' => 'required|boolean',
            'categories' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'categories.required' => 'Faltó completar el campo "CATEGORÍA"',
            'categories.*.exists' => 'La categoría seleccionada no es válida.',
        ];
    }    

}

