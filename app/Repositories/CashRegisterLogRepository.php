<?php

namespace App\Repositories;

use App\Models\CashRegisterLog;
use Yajra\DataTables\DataTables;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\CashRegister;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;



class CashRegisterLogRepository
{

    public function hasOpenLogForUser(int $userId): ?int
    {
        $openLog = CashRegisterLog::whereNull('close_time')
            ->whereHas('cashRegister', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();
    
        return $openLog ? $openLog->cash_register_id : null;
    }
    

    /**
     * Obtiene todos los registros del log de una caja dado su ID.
     *
     * @param int $id
     * @return CashRegisterLog|null
    */
    public function getLogsFromACashRegister(int $id): ?CashRegisterLog
    {
        return CashRegisterLog::find($id);
    }

    /**
     * Actualiza un registro de caja existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
    */
    public function updateCashRegisterLog(int $id, array $data): bool
    {
        $cashRegisterLog = CashRegisterLog::find($id);
        if ($cashRegisterLog) {
            return $cashRegisterLog->update($data);
        }
        return false;
    }

    /**
     * Elimina un registro de log de una caja por su ID.
     *
     * @param int $id
     * @return bool
    */
    public function deleteCashRegisterLog(int $id): bool
    {
        $cashRegisterLog = CashRegisterLog::find($id);
        if ($cashRegisterLog) {
            return $cashRegisterLog->delete();
        }
        return false;
    }

    /**
     * Crea un nuevo registro de log de una caja registradora.
     *
     * @param array $data
     * @return CashRegisterLog
    */
    public function createCashRegisterLog(array $data): CashRegisterLog
    {
        if (!isset($data['open_time']) || !$data['open_time'] instanceof \Carbon\Carbon) {
            $data['open_time'] = now();
        }
        return CashRegisterLog::create($data);
    
    }

    /**
    * Cierra la caja registradora.
    *
    * @param Store $store
    * @return RedirectResponse
    */
    public function closeCashRegister(int $id): ?bool
    {
        $openLog = CashRegisterLog::where('cash_register_id', $id)
            ->whereNull('close_time')
            ->first();

        if (!$openLog || $openLog->close_time) {
            return false;
        }

        try {
            // Cerrar caja
            $openLog->close_time = now();

            // Calcular ventas en efectivo y POS
            $totalSales = \DB::table('pos_orders')
                ->selectRaw('SUM(cash_sales) as total_cash_sales, SUM(pos_sales) as total_pos_sales')
                ->where('cash_register_log_id', $openLog->id)
                ->whereBetween(\DB::raw('CONCAT(date, " ", hour)'), [$openLog->open_time, $openLog->close_time])
                ->first();

            $openLog->cash_sales = $totalSales->total_cash_sales ?? 0;
            $openLog->pos_sales = $totalSales->total_pos_sales ?? 0;

            $openLog->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Verifica si hay un log abierto para una caja registradora específica.
     *
     * @param int $cashRegisterId
     * @return bool
     */
    public function hasOpenLog(): bool
    {
        return CashRegisterLog::whereNull('close_time')
            ->exists();
    }

    /**
     * Toma los productos de la tienda de la caja registradora.
     * 
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProductsForPOS(int $id)
    {
        $cashRegister = CashRegister::find($id);
        
        if (!$cashRegister) {
            throw new \Exception('Cash register not found');
        }

        $storeId = $cashRegister->store_id;

        $products = Product::where('store_id', $storeId)->get();

        return $products;
    }

    /**
     * Toma los sabores para crear los productos con varios sabores.
     * 
     * @return Flavor
     */
    public function getFlavors()
    {
        $flavors = Flavor::all();
        return $flavors;
    }
    

    /**
     * Toma las categorías padres.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        return DB::table('category_product')->get();
    }

}
