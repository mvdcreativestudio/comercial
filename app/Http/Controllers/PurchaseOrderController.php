<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Repositories\PurchaseOrderRepository;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    protected $purchaseOrderRepository;

    public function __construct(PurchaseOrderRepository $purchaseOrderRepository)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
    }

    public function index()
    {
        $purchaseOrders = $this->purchaseOrderRepository->getAll();
        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        return view('purchase_orders.create');
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $purchaseOrder = $this->purchaseOrderRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Orden de compra creada con éxito.',
                'purchaseOrder' => $purchaseOrder
            ]);
        }

        return redirect()->route('purchase-orders.index')->with('success', 'Orden de compra creada con éxito.');
    }



    public function show($id)
    {
        $purchaseOrder = $this->purchaseOrderRepository->find($id);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit($id)
    {
        $purchaseOrder = $this->purchaseOrderRepository->find($id);
        return view('purchase_orders.edit', compact('purchaseOrder'));
    }

    public function update(UpdatePurchaseOrderRequest $request, $id)
    {
        $this->purchaseOrderRepository->update($id, $request->validated());
        return redirect()->route('purchase-orders.index')->with('success', 'Orden de compra actualizada con éxito.');
    }

    public function destroy($id)
    {
        $deleted = $this->purchaseOrderRepository->delete($id);
        if ($deleted) {
            return response()->json(['message' => 'Log de orden de compra borrada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el log de la orden de compra que se deseó borrar.'], 404);
        }    
    }
}
