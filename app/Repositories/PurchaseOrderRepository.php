<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;

class PurchaseOrderRepository
{
    public function getAll()
    {
        return $purchaseOrders = PurchaseOrder::select('purchase_orders.*', 'suppliers.name as supplier_name')
        ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
        ->get();
    }

    public function find($id)
    {
        return PurchaseOrder::findOrFail($id);
    }

    public function create(array $data)
    {
        return PurchaseOrder::create($data);
    }

    public function update($id, array $data)
    {
        $purchaseOrder = $this->find($id);
        $purchaseOrder->update($data);
        return $purchaseOrder;
    }

    public function delete($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        return $purchaseOrder->delete();
    }
}
