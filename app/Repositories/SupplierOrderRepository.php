<?php

namespace App\Repositories;

use App\Models\SupplierOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\RawMaterial;

class SupplierOrderRepository
{
    /**
     * Devuelve todas las órdenes de compra.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll(): Collection
    {
        if (auth()->user()->can('view_all_supplier-orders')) {
            return SupplierOrder::with(['supplier', 'rawMaterials', 'store'])->get();
        } else {
            $storeId = auth()->user()->store_id;
            return SupplierOrder::with(['supplier', 'rawMaterials'])->where('store_id', $storeId)->get();
        }
    }


    /**
     * Busca una orden de compra por el ID.
     *
     * @param  int $id
     * @return SupplierOrder|null
    */
    public function findById($id): ?SupplierOrder
    {
        return SupplierOrder::with('supplier')->where('id', $id)->first();
    }

    /**
     * Guarda una nueva orden de compra.
     *
     * @param  array $data
     * @return SupplierOrder
    */
    public function create(array $data): SupplierOrder
    {
        $data['store_id'] = auth()->user()->store_id ?? throw new ModelNotFoundException('No se puede crear una orden de compra sin una tienda asignada.');

        $order = SupplierOrder::create($data);

        $totalOrderCost = 0;

        if (isset($data['raw_material_id']) && isset($data['quantity']) && isset($data['unit_cost'])) {
            foreach ($data['raw_material_id'] as $index => $rawMaterialId) {
                $quantity = $data['quantity'][$index];
                $unitCost = $data['unit_cost'][$index];
                $totalCost = $quantity * $unitCost;

                if ($quantity > 0) {
                    // Agrega la relación en la tabla pivot 'supplier_order_raw_material'
                    $order->rawMaterials()->attach($rawMaterialId, [
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'total_cost' => $totalCost,
                    ]);

                    // Actualiza el stock en la tabla pivot 'raw_material_store'
                    if ($order->shipping_status == 'completed') {
                        $store = auth()->user()->store;
                        $store->rawMaterials()->updateExistingPivot($rawMaterialId, [
                            'stock' => \DB::raw('stock + ' . $quantity)
                        ]);
                    }

                    // Acumula el total de la orden
                    $totalOrderCost += $totalCost;
                }
            }
        }

        // Actualiza el campo 'total' en la tabla supplier_orders
        $order->update(['total' => $totalOrderCost]);

        return $order;
    }



    /**
     * Actualiza una orden de compra existente y ajusta el stock de las materias primas si es necesario.
     *
     * @param  int $id
     * @param  array $data
     * @return SupplierOrder
    */
    public function update(int $id, array $data): SupplierOrder
    {
        $supplierOrder = SupplierOrder::findOrFail($id);

        // Guarda el estado anterior de 'completed' antes de cualquier actualización.
        $wasCompleted = $supplierOrder->shipping_status == 'completed';

        // Actualiza la orden con los nuevos datos proporcionados.
        $supplierOrder->update($data);

        // Obtiene las cantidades actuales de las materias primas antes de cualquier actualización.
        $currentMaterials = $supplierOrder->rawMaterials()->pluck('quantity', 'raw_material_id')->toArray();

        $totalOrderCost = 0;

        if (!isset($data['raw_material_id']) || !isset($data['quantity']) || !isset($data['unit_cost']) ||
            count($data['raw_material_id']) == 0 || count($data['quantity']) == 0) {
            // Si no se proporcionan materias primas, asume que todas deben eliminarse.
            if ($wasCompleted) {
                // Decrementa el stock de todas las materias primas si la orden estaba completada.
                foreach ($currentMaterials as $materialId => $quantity) {
                    $store = auth()->user()->store;
                    $store->rawMaterials()->updateExistingPivot($materialId, [
                        'stock' => \DB::raw('stock - ' . $quantity)
                    ]);
                    $supplierOrder->rawMaterials()->detach($materialId);
                }
            }
        } else {
            $updatedMaterials = array_combine($data['raw_material_id'], $data['quantity']);

            foreach ($updatedMaterials as $materialId => $quantity) {
                $quantity = (int) $quantity;
                $unitCost = $data['unit_cost'][array_search($materialId, $data['raw_material_id'])];
                $totalCost = $quantity * $unitCost;

                $store = auth()->user()->store;

                if (isset($currentMaterials[$materialId])) {
                    if ($wasCompleted) {
                        if ($supplierOrder->shipping_status !== 'completed') {
                            // Si la orden estaba completada pero ya no lo está, decrementa el stock.
                            $store->rawMaterials()->updateExistingPivot($materialId, [
                                'stock' => \DB::raw('stock - ' . $quantity)
                            ]);
                        } else {
                            // Si la orden sigue completada, ajusta el stock según la diferencia de cantidades.
                            $difference = $quantity - $currentMaterials[$materialId];
                            if ($difference != 0) {
                                $store->rawMaterials()->updateExistingPivot($materialId, [
                                    'stock' => \DB::raw('stock + ' . $difference)
                                ]);
                            }
                        }
                    } elseif ($supplierOrder->shipping_status == 'completed') {
                        // Si la orden no estaba completada antes pero ahora sí lo está, incrementa el stock.
                        $store->rawMaterials()->updateExistingPivot($materialId, [
                            'stock' => \DB::raw('stock + ' . $quantity)
                        ]);
                    }

                    $supplierOrder->rawMaterials()->updateExistingPivot($materialId, [
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'total_cost' => $totalCost,
                    ]);
                } else {
                    $supplierOrder->rawMaterials()->attach($materialId, [
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'total_cost' => $totalCost,
                    ]);

                    if ($supplierOrder->shipping_status == 'completed') {
                        $store->rawMaterials()->updateExistingPivot($materialId, [
                            'stock' => \DB::raw('stock + ' . $quantity)
                        ]);
                    }
                }

                $totalOrderCost += $totalCost;
                unset($currentMaterials[$materialId]);
            }

            // Manejo de materias primas eliminadas.
            foreach ($currentMaterials as $materialId => $quantity) {
                if ($wasCompleted) {
                    $store->rawMaterials()->updateExistingPivot($materialId, [
                        'stock' => \DB::raw('stock - ' . $quantity)
                    ]);
                }
                $supplierOrder->rawMaterials()->detach($materialId);
            }
        }

        // Actualiza el campo 'total' en la tabla supplier_orders
        $supplierOrder->update(['total' => $totalOrderCost]);

        return $supplierOrder;
    }



    /**
     * Elimina una orden de compra y ajusta el stock de las materias primas si la orden está completada.
     *
     * @param  SupplierOrder $supplierOrder
     * @return bool|null
    */
    public function delete($id)
    {
        $supplierOrder = SupplierOrder::with('rawMaterials')->findOrFail($id);

        // Verifica si la orden estaba completada antes de la eliminación.
        $wasCompleted = $supplierOrder->shipping_status == 'completed';

        if ($wasCompleted) {
            // Ajusta el stock de cada materia prima asociada si la orden estaba completada.
            foreach ($supplierOrder->rawMaterials as $rawMaterial) {
                $quantity = $rawMaterial->pivot->quantity;
                $rawMaterial->decrement('stock', $quantity);
            }
        }

        // Elimina la orden y todas sus relaciones.
        // Esto se puede asegurar definiendo la eliminación en cascada en la base de datos o
        // manualmente aquí antes de eliminar la orden en sí.
        $supplierOrder->rawMaterials()->detach(); // Esto elimina las relaciones con las materias primas.
        $supplierOrder->delete(); // Elimina la orden de compra.

        return response()->json(['message' => 'Orden eliminada con éxito.']);
    }

    /**
     * Obtiene los detalles completos de una orden de compra para generar un PDF.
     *
     * @param  int  $id
     * @return SupplierOrder
     */
    public function findOrderDetailsForPdf(int $id): SupplierOrder
    {
        return SupplierOrder::with(['supplier', 'rawMaterials', 'store'])
                            ->findOrFail($id);
    }
}
