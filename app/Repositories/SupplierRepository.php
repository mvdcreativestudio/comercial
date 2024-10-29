<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierRepository
{
    /**
     * Devuelve todos los proveedores.
     *
     * @return array
     */
    public function getAllWithOrders(): array
    {
        if (auth()->user() && auth()->user()->can('view_all_suppliers')) {
          $suppliers = Supplier::all();
        } 

        $recentOrders = $suppliers->map(function ($supplier) {
            return $supplier->orders->sortByDesc('created_at')->first();
        });

        return compact('suppliers', 'recentOrders');
    }


    /**
     * Devuelve todos los proveedores.
     *
     */
    public function getAll()
    {
        return Supplier::all();
    }

    /**
     * Busca proveedores por el store_id
     *
     * @param int $store_id
     * @return Collection
    */
    public function findByStoreId($store_id): Collection
    {
        return Supplier::where('store_id', $store_id)->get();
    }

    /**
     * Busca todos los proveedores
     *
     * @return Collection
    */
    public function findAll(): Collection
    {
        return Supplier::all();
    }

    /**
     * Guarda un nuevo proveedor.
     *
     * @param array $data
     * @return Supplier
     */
    public function create(array $data): Supplier
    {
        $data['store_id'] = auth()->user()->store_id ?? throw new ModelNotFoundException('No se puede crear un proveedor sin una tienda asignada.');

        return Supplier::create($data);
    }

    /**
     * Actualiza un proveedor existente.
     *
     * @param Supplier $supplier
     * @param array $data
     * @return Supplier
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);
        return $supplier;
    }

    /**
     * Elimina un proveedor.
     *
     * @param Supplier $supplier
     * @return bool|null
     */
    public function delete(Supplier $supplier): ?bool
    {
        return $supplier->delete();
    }
}
