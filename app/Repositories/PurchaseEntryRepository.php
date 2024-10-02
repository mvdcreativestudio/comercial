<?php

namespace App\Repositories;

use App\Models\PurchaseEntry;
use App\Models\Batch;

class PurchaseEntryRepository
{
    public function getAllByPurchaseOrderItems(array $purchaseOrderItemIds)
    {
        return PurchaseEntry::whereIn('purchase_order_items_id', $purchaseOrderItemIds)
            ->with(['batches' => function ($query) {
                $query->select('purchase_entries_id'); // Solo necesitamos el ID para verificar la existencia de lotes
            }])
            ->get()
            ->map(function ($entry) {
                $entry->has_batches = $entry->batches->isNotEmpty(); // Indicador de si tiene lotes
                return $entry;
            });
    }


    public function getAll()
    {
        return PurchaseEntry::all();
    }

    public function find($id)
    {
        return PurchaseEntry::findOrFail($id);
    }

    public function create(array $data)
    {
        return PurchaseEntry::create($data);
    }

    public function update($id, array $data)
    {
        $purchaseEntry = $this->find($id);
        $purchaseEntry->update($data);
        return $purchaseEntry;
    }

    public function delete($id)
    {
        $purchaseEntry = $this->find($id);
        return $purchaseEntry->delete();
    }
}
