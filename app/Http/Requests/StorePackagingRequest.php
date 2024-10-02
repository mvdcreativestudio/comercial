<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackagingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'bulk_production_id' => 'required|integer|exists:bulk_productions,id',
            'quantity_packaged' => 'required|integer|min:1',
            'packaging_date' => 'required|date',
            'final_product_id' => 'required|integer|exists:final_products,id',
        ];
    }
}
