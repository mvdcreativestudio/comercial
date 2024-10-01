<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;


class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Agrega un log para ver los datos que vienen del Excel
        \Log::info('Fila importada:', $row);

        return new Product([
            'name' => $row['name'],
            'description' => $row['description'],
            'type' => $row['type'],
            'old_price' => $row['old_price'],
            'price' => $row['price'],
            'stock' => $row['stock'],
            'store_id' => $row['store_id'],
            'image' => $row['image'],
            'status' => in_array($row['status'], ['Sí', 'Si']) ? 1 : 0,
            'draft' => in_array($row['draft'], ['Sí', 'Si']) ? 1 : 0,
            'safety_margin' => $row['safety_margin'],
        ]);
    }


    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'sku' => 'nullable|string',
            'description' => 'nullable|string',
            'old_price' => 'required|numeric',
            'price' => 'nullable|numeric',
            'image' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'max_flavors' => 'nullable|integer',
            'type' => 'required|in:simple,configurable',
            'stock' => 'nullable|integer',
            'safety_margin' => 'nullable|numeric',
            'store_id' => 'required|exists:stores,id',
            'status' => 'required|in:Si,No',
            'draft' => 'nullable|in:Si,No',
            'is_trash' => 'nullable|in:Si,No',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
        ];
    }
}
