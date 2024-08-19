<?php

namespace App\Repositories;
use App\Models\Store;

use App\Models\CashRegister;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\ProductCategory;



class CashRegisterRepository
{
    /**
     * Obtiene todos los registros de caja registradora para la tabla de datos.
     *
     * @return mixed
    */
    public function getCashRegistersForDatatable($userId): mixed
    {
        if (!Auth::user()->hasRole('Administrador')) {
            $query = CashRegister::select(['id', 'store_id', 'user_id'])
            ->where('user_id', $userId)
            ->get();

           return $query;
        }
    
        $query = CashRegister::select(['id', 'store_id', 'user_id'])
                     ->get();
        
        return $query;
    }


    /**
     * Crea un nuevo registro de caja.
     *
     * @param array $data
     * @return CashRegister
    */
    public function createCashRegister(array $data): CashRegister
    {
        return CashRegister::create($data);
    }

    /**
     * Obtiene un registro de caja por su ID.
     *
     * @param int $id
     * @return CashRegister|null
    */
    public function getCashRegisterById(int $id): ?CashRegister
    {
        return CashRegister::find($id);
    }

    /**
     * Actualiza un registro de caja existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
    */
    public function updateCashRegister(int $id, array $data): bool
    {
        $cashRegister = CashRegister::find($id);
        if ($cashRegister) {
            return $cashRegister->update($data);
        }
        return false;
    }

    /**
     * Elimina un registro de caja por su ID.
     *
     * @param int $id
     * @return bool
    */
    public function deleteCashRegister(int $id): bool
    {
        $cashRegister = CashRegister::find($id);
        if ($cashRegister) {
            return $cashRegister->delete();
        }
        return false;
    }

    /**
     * Devuelve la(s) tienda(s) a las cuales le puede abrir una caha registradora.
     */
    public function storesForCashRegister()
    {
        if (!Auth::user()->hasRole('Administrador')) {
            return auth()->user()->store_id;
        } else {
            return Store::pluck('id');
        }
    }
}
