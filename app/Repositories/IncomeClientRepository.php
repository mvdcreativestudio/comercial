<?php

namespace App\Repositories;

use App\Helpers\Helpers;
use App\Models\Client;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class IncomeClientRepository
{
    /**
     * Obtiene todos los ingresos.
     *
     * @return mixed
     */
    public function getAllIncomes(): mixed
    {
        $incomes = Income::where('client_id', '!=', null)->get();
        $totalIncomes = Income::where('client_id', '!=', null)->count();
        $totalIncomeAmount = Income::where('client_id', '!=', null)->sum('income_amount');
        $paymentMethods = PaymentMethod::all();
        $incomeCategories = IncomeCategory::all();
        $clients = Client::all();
        return compact('incomes', 'totalIncomes', 'totalIncomeAmount', 'paymentMethods', 'incomeCategories', 'clients');
    }

    /**
     * Almacena un nuevo ingreso en la base de datos.
     *
     * @param  array  $data
     * @return Income
     */
    public function store(array $data): Income
    {
        DB::beginTransaction();

        try {
            // Crear el nuevo ingreso
            $income = Income::create([
                'income_name' => $data['income_name'],
                'income_description' => $data['income_description'] ?? null,
                'income_date' => $data['income_date'],
                'income_amount' => $data['income_amount'],
                'payment_method_id' => $data['payment_method_id'],
                'income_category_id' => $data['income_category_id'],
                'client_id' => $data['client_id'] ?? null,
            ]);

            DB::commit();
            return $income;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene un ingreso específico por su ID.
     *
     * @param int $incomeId
     * @return Income
     */
    public function getIncomeById(int $incomeId): Income
    {
        return Income::findOrFail($incomeId);
    }

    /**
     * Actualiza un ingreso específico en la base de datos.
     *
     * @param int $incomeId
     * @param array $data
     * @return Income
     */
    public function update(int $incomeId, array $data): Income
    {
        DB::beginTransaction();

        try {
            // Buscar y actualizar el ingreso
            $income = Income::findOrFail($incomeId);
            $income->update([
                'income_name' => $data['income_name'],
                'income_description' => $data['income_description'] ?? null,
                'income_date' => $data['income_date'],
                'income_amount' => $data['income_amount'],
                'payment_method_id' => $data['payment_method_id'],
                'income_category_id' => $data['income_category_id'],
                'client_id' => $data['client_id'] ?? null,
            ]);

            DB::commit();
            return $income;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un ingreso específico.
     *
     * @param int $incomeId
     * @return void
     */
    public function destroyIncome(int $incomeId): void
    {
        $income = Income::findOrFail($incomeId);
        $income->delete();
    }

    /**
     * Elimina varios ingresos.
     *
     * @param array $incomeIds
     * @return void
     */
    public function deleteMultipleIncomes(array $incomeIds): void
    {
        DB::beginTransaction();

        try {
            // Eliminar los ingresos
            Income::whereIn('id', $incomeIds)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene los ingresos para la DataTable.
     *
     * @param Request $request
     * @return mixed
     */
    public function getIncomesForDataTable(Request $request): mixed
    {
        $query = Income::select([
            'incomes.id',
            'incomes.income_name',
            'incomes.income_description',
            'incomes.income_date',
            'incomes.income_amount',
            'incomes.payment_method_id',
            'incomes.income_category_id',
            'incomes.client_id',
            'incomes.supplier_id',
            'incomes.created_at',
            'clients.name as client_name',
            'income_categories.income_name as income_category_name',
            'payment_methods.description as payment_method_name',
        ])
            ->join('clients', 'incomes.client_id', '=', 'clients.id')
            ->join('income_categories', 'incomes.income_category_id', '=', 'income_categories.id')
            ->leftJoin('payment_methods', 'incomes.payment_method_id', '=', 'payment_methods.id')
            ->orderBy('incomes.created_at', 'desc');

        // Verificar permisos del usuario
        if (!Auth::user()->can('view_all_incomes')) {
            $query->where(function ($query) {
                $query->where('incomes.client_id', Auth::user()->client_id)
                    ->orWhereNull('incomes.client_id'); // Incluir registros con client_id null
            });
        }

        // Filtrar por rango de fechas
        if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('incomes.income_date', [$startDate, $endDate]);
        }

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }
}
