<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncomeClientRequest;
use App\Http\Requests\UpdateIncomeClientRequest;
use App\Repositories\IncomeClientRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class IncomeClientController extends Controller
{
    /**
     * El repositorio para las operaciones de ingresos.
     *
     * @var IncomeClientRepository
     */
    protected $incomeClientRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param IncomeClientRepository $incomeClientRepository
     */
    public function __construct(IncomeClientRepository $incomeClientRepository)
    {
        $this->middleware(['check_permission:access_incomes-clients', 'user_has_store'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_incomes-clients'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->incomeClientRepository = $incomeClientRepository;
    }

    /**
     * Muestra una lista de todos los ingresos.
     *
     * @return View
     */
    public function index(): View
    {
        $incomes = $this->incomeClientRepository->getAllIncomes();
        // dd($incomes);
        return view('content.accounting.incomes.incomes-clients.index', $incomes);
    }

    /**
     * Muestra el formulario para crear un nuevo ingreso.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.accounting.incomes.create-income');
    }

    /**
     * Almacena un nuevo ingreso en la base de datos.
     *
     * @param StoreIncomeClientRequest $request
     * @return JsonResponse
     */
    public function store(StoreIncomeClientRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $income = $this->incomeClientRepository->store($validated);
            return response()->json(['success' => true, 'data' => $income]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar el ingreso.'], 400);
        }
    }

    /**
     * Muestra un ingreso específico.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $income = $this->incomeClientRepository->getIncomeById($id);
        return view('content.accounting.incomes.details-income', compact('income'));
    }

    /**
     * Devuelve los datos de un ingreso específico para edición.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $income = $this->incomeClientRepository->getIncomeById($id);
            return response()->json($income);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos del ingreso.'], 400);
        }
    }

    /**
     * Actualiza un ingreso específico.
     *
     * @param UpdateIncomeClientRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateIncomeClientRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $income = $this->incomeClientRepository->update($id, $validated);
            return response()->json(['success' => true, 'data' => $income]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el ingreso.'], 400);
        }
    }

    /**
     * Elimina un ingreso específico.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->incomeClientRepository->destroyIncome($id);
            return response()->json(['success' => true, 'message' => 'Ingreso eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el ingreso.'], 400);
        }
    }

    /**
     * Elimina varios ingresos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->incomeClientRepository->deleteMultipleIncomes($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Ingresos eliminados correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los ingresos.'], 400);
        }
    }

    /**
     * Obtiene los ingresos para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->incomeClientRepository->getIncomesForDataTable($request);
    }
}
