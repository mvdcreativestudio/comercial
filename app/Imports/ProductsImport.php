<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading
{
    protected $storeId;

    public function __construct($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Mapear las cabeceras en español a los campos de la base de datos.
     */
    private function mapColumns(array $row)
    {
        return [
            'name' => $row['nombre'] ?? null,
            'description' => $row['descripción'] ?? null,
            'type' => $row['tipo'] ?? 'simple',
            'old_price' => isset($row['precio_antiguo']) ? floatval($row['precio_antiguo']) : 0,
            'price' => isset($row['precio']) ? floatval($row['precio']) : null,
            'stock' => isset($row['stock']) ? intval($row['stock']) : 0,
            'image' => $row['imagen'] ?? '/assets/img/ecommerce-images/placeholder.png',
            'status' => isset($row['estado']) ? (in_array(strtolower($row['estado']), ['sí', 'si']) ? 1 : 0) : 1,
            'draft' => isset($row['borrador']) ? (in_array(strtolower($row['borrador']), ['sí', 'si']) ? 1 : 0) : 0,
            'safety_margin' => isset($row['margen_seguridad']) ? floatval($row['margen_seguridad']) : 0,
        ];
    }

    /**
     * Limpieza y procesamiento de la fila de Excel
     */
    public function model(array $row)
    {
        // Log para monitorear la importación
        Log::info('Fila importada:', $row);

        // Mapear las columnas del Excel (en español) a los campos de la base de datos
        $mappedRow = $this->mapColumns($row);

        return new Product([
            'name' => $mappedRow['name'],
            'description' => $mappedRow['description'],
            'type' => $mappedRow['type'],
            'old_price' => $mappedRow['old_price'],
            'price' => $mappedRow['price'],
            'stock' => $mappedRow['stock'],
            'store_id' => $this->storeId,
            'image' => $mappedRow['image'],
            'status' => $mappedRow['status'],
            'draft' => $mappedRow['draft'],
            'safety_margin' => $mappedRow['safety_margin'],
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255', // 'name' en DB
            'sku' => 'nullable|string|max:50',
            'descripción' => 'nullable|string', // 'description' en DB
            'precio_antiguo' => 'required|numeric|min:0', // 'old_price' en DB
            'precio' => 'nullable|numeric|min:0', // 'price' en DB
            'imagen' => 'nullable|string|max:255', // 'image' en DB
            'descuento' => 'nullable|numeric|min:0', // 'discount' en DB
            'max_sabores' => 'nullable|integer|min:0', // 'max_flavors' en DB
            'tipo' => 'nullable|in:simple,configurable', // 'type' en DB
            'stock' => 'nullable|integer|min:0', // 'stock' en DB
            'margen_seguridad' => 'nullable|numeric|min:0', // 'safety_margin' en DB
            'estado' => 'nullable|in:Si,No', // 'status' en DB
            'borrador' => 'nullable|in:Si,No', // 'draft' en DB
            'is_trash' => 'nullable|in:Si,No',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
        ];
    }

    public function chunkSize(): int
    {
        return 100; // Define el tamaño de chunk a procesar para optimizar memoria
    }

    public function customValidationMessages()
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'precio_antiguo.required' => 'El precio anterior es obligatorio.',
            'precio.numeric' => 'El precio debe ser un valor numérico.',
            'estado.required' => 'El estado es obligatorio y debe ser Sí o No.',
        ];
    }
}
