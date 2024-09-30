<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncomeSupplierRequest;
use App\Http\Requests\UpdateIncomeSupplierRequest;
use App\Repositories\IncomeSupplierRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class IncomeSupplierController extends Controller
{
    /**
     * El repositorio para las operaciones de ingresos de proveedores.
     *
     * @var IncomeSupplierRepository
     */
    protected $incomeSupplierRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param IncomeSupplierRepository $incomeSupplierRepository
     */
    public function __construct(IncomeSupplierRepository $incomeSupplierRepository)
    {
        $this->middleware(['check_permission:access_incomes-suppliers', 'user_has_store'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_incomes-suppliers'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->incomeSupplierRepository = $incomeSupplierRepository;
    }

    /**
     * Muestra una lista de todos los ingresos de proveedores.
     *
     * @return View
     */
    public function index(): View
    {
        $incomes = $this->incomeSupplierRepository->getAllIncomes();
        return view('content.accounting.incomes.incomes-suppliers.index', $incomes);
    }

    /**
     * Muestra el formulario para crear un nuevo ingreso de proveedor.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.accounting.incomes.create-income');
    }

    /**
     * Almacena un nuevo ingreso de proveedor en la base de datos.
     *
     * @param StoreIncomeSupplierRequest $request
     * @return JsonResponse
     */
    public function store(StoreIncomeSupplierRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $income = $this->incomeSupplierRepository->store($validated);
            return response()->json(['success' => true, 'data' => $income]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar el ingreso.'], 400);
        }
    }

    /**
     * Muestra un ingreso específico de proveedor.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $income = $this->incomeSupplierRepository->getIncomeById($id);
        return view('content.accounting.incomes.details-income', compact('income'));
    }

    /**
     * Devuelve los datos de un ingreso específico de proveedor para edición.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $income = $this->incomeSupplierRepository->getIncomeById($id);
            return response()->json($income);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos del ingreso.'], 400);
        }
    }

    /**
     * Actualiza un ingreso específico de proveedor.
     *
     * @param UpdateIncomeSupplierRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateIncomeSupplierRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $income = $this->incomeSupplierRepository->update($id, $validated);
            return response()->json(['success' => true, 'data' => $income]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el ingreso.'], 400);
        }
    }

    /**
     * Elimina un ingreso específico de proveedor.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->incomeSupplierRepository->destroyIncome($id);
            return response()->json(['success' => true, 'message' => 'Ingreso eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el ingreso.'], 400);
        }
    }

    /**
     * Elimina varios ingresos de proveedores.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->incomeSupplierRepository->deleteMultipleIncomes($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Ingresos eliminados correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los ingresos.'], 400);
        }
    }

    /**
     * Obtiene los ingresos de proveedores para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->incomeSupplierRepository->getIncomesForDataTable($request);
    }
}
