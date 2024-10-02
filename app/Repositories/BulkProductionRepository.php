<?php

namespace App\Repositories;

use App\Models\BulkProduction;
use App\Models\Batch;
use App\Models\FormulaRawMaterial;
use App\Models\BulkProductionBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkProductionRepository
{
    public function getAll()
    {
        return BulkProduction::all();
    }

    public function find($id)
    {
        return BulkProduction::findOrFail($id);
    }

    public function create(array $data)
    {
        return BulkProduction::create($data);
    }

    public function update($id, array $data)
    {
        $bulkProduction = $this->find($id);
        $bulkProduction->update($data);
        return $bulkProduction;
    }

    public function delete($id)
    {
        $bulkProduction = BulkProduction::find($id);
        return $bulkProduction->delete();
    }

    public function startProduction(int $formulaId, float $quantity): array
    {
        return DB::transaction(function () use ($formulaId, $quantity) {
            // 1. Se toman todas las materias primas de la fórmula.
            $formulaRawMaterials = FormulaRawMaterial::where('formula_id', $formulaId)->get();

            // 2. Calcula la cantidad que se requiere y si está disponible dicha cantidad.
            $requiredMaterials = [];
            foreach ($formulaRawMaterials as $material) {
                if ($material->quantity_required != null) {
                    $requiredQuantity = $material->quantity_required * $quantity;
                }

                // Agrupa por paso (step) y agrega aclaraciones
                $requiredMaterials[$material->step]['clarifications'][] = $material->clarification;
                $requiredMaterials[$material->step]['materials'][] = [
                    'raw_material_id' => $material->raw_material_id,
                    'required_quantity' => $requiredQuantity,
                ];
            }

            // 3. Crea el elemento de la producción en la tabla.
            $bulkProduction = BulkProduction::create([
                'formula_id' => $formulaId,
                'quantity_produced' => $quantity,
                'production_date' => Carbon::now(),
                'quantity_used' => 0
            ]);

            // 4. Procesa cada paso (step) con sus materias primas y registra los lotes (batches) utilizados.
            $stepBatchUsage = [];
            foreach ($requiredMaterials as $step => $stepData) {
                $batchesUsed = [];

                // Procesar solo las materias primas que tienen id y cantidad requerida
                if (!empty($stepData['materials'])) {
                    foreach ($stepData['materials'] as $material) {
                        // Solo procesar si hay materia prima asociada (no nulo)
                        if ($material['raw_material_id'] !== null) {
                            $materialBatches = $this->processMaterial($material, $bulkProduction->id);
                            $batchesUsed = array_merge($batchesUsed, $materialBatches);
                        }
                    }
                }

                // Combina aclaraciones y añade los lotes usados a cada paso
                $stepBatchUsage[] = [
                    'step' => $step,
                    'clarifications' => implode(", ", $stepData['clarifications'] ?? []),
                    'batches_used' => $batchesUsed // Puede ser un array vacío si no hay lotes
                ];
            }

            // 5. Devuelve la información de los pasos y los lotes utilizados.
            return [
                'success' => true,
                'bulk_productions_id' => $bulkProduction->id,
                'step_batch_usage' => $stepBatchUsage
            ];
        });
    }



    private function processMaterial(array $material, int $bulkProductionId): array
    {
        $remainingQuantity = $material['required_quantity'];
        $batchesUsed = [];

        while ($remainingQuantity > 0) {
            $batch = $this->getOldestBatch($material['raw_material_id']);

            if (!$batch) {
                throw new \Exception("No available batch for raw material ID: {$material['raw_material_id']}");
            }

            // Verificación del Lote y la cantidad.
            $batch = Batch::find($batch->id);
            if (!$batch || $batch->quantity <= 0) {
                continue;
            }

            $quantityToUse = min($remainingQuantity, $batch->quantity);

            try {
                // Crea el elemento en tabla que indica lote utilizado.
                $bulkProductionBatch = new BulkProductionBatch();
                $bulkProductionBatch->bulk_productions_id = $bulkProductionId;
                $bulkProductionBatch->batch_id = $batch->id;
                $bulkProductionBatch->quantity_used = $quantityToUse;
                $bulkProductionBatch->save();

                // Cantidad del batch actualizado.
                $batch->quantity -= $quantityToUse;
                $batch->save();

                $remainingQuantity -= $quantityToUse;

                // Registrar el uso del batch en este paso
                $batchesUsed[] = [
                    'batch_id' => $batch->id,
                    'quantity_used' => $quantityToUse
                ];
            } catch (\Exception $e) {
                Log::error("Error procesando el batch {$batch->id}: " . $e->getMessage());
                continue;
            }
        }

        if ($remainingQuantity > 0) {
            throw new \Exception("No se encontro materia prima disponible para la materia prima: {$material['raw_material_id']}");
        }

        return $batchesUsed;
    }

    private function getOldestBatch(int $rawMaterialId)
    {
        return Batch::join('purchase_entries', 'batches.purchase_entries_id', '=', 'purchase_entries.id')
            ->join('purchase_order_items', 'purchase_entries.purchase_order_items_id', '=', 'purchase_order_items.id')
            ->where('purchase_order_items.raw_material_id', $rawMaterialId)
            ->where('batches.quantity', '>', 0)
            ->orderBy('batches.expiration_date')
            ->select('batches.*')
            ->first();
    }


    private function getAvailableQuantity(int $rawMaterialId): float
    {
        return Batch::join('purchase_entries', 'batches.purchase_entries_id', '=', 'purchase_entries.id')
            ->join('purchase_order_items', 'purchase_entries.purchase_order_items_id', '=', 'purchase_order_items.id')
            ->where('purchase_order_items.raw_material_id', $rawMaterialId)
            ->sum('batches.quantity');
    }
}
