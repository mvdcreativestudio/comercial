<?php

namespace App\Repositories;

use App\Models\FormulaRawMaterial;

class FormulaRawMaterialRepository
{
    public function getAll($id)
    {
        return FormulaRawMaterial::where('formula_id', $id)
            ->get();
    }

    public function find($id)
    {
        return FormulaRawMaterial::findOrFail($id);
    }

    public function create(array $data)
    {
        return FormulaRawMaterial::create($data);
    }

    public function update($id, array $data)
    {
        $formulaRawMaterial = $this->find($id);
        $formulaRawMaterial->update($data);
        return $formulaRawMaterial;
    }

    public function delete($id)
    {
        $formulaRawMaterial = FormulaRawMaterial::find($id);
        return $formulaRawMaterial->delete();
    }

    public function bulkInsert(array $rows, $formulaId)
    {
        foreach ($rows as $row) {
            FormulaRawMaterial::create([
                'formula_id' => $formulaId,
                'raw_material_id' => $row['raw_material_id'],
                'quantity_required' => $row['quantity_required'],
                'step' => $row['step'],
                'clarification' => $row['clarification'] ?? null,
            ]);
        }
    }
}
