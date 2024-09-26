<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrentAccountSupplierRequest;
use App\Http\Requests\UpdateCurrentAccountRequest;
use App\Http\Requests\UpdateCurrentAccountSupplierRequest;
use App\Repositories\CurrentAccountSupplierPurchaseRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrentAccountSupplierPurchaseController extends Controller
{
    /**
     * El repositorio para las operaciones de cuentas corrientes.
     *
     * @var CurrentAccountSupplierPurchaseRepository
     */
    protected $currentAccountSupplierPurchaseRepository;

    /**
     * Inyecta los repositorios en el controlador y los middleware.
     *
     * @param CurrentAccountSupplierPurchaseRepository $currentAccountSupplierPurchaseRepository
     */
    public function __construct(CurrentAccountSupplierPurchaseRepository $currentAccountSupplierPurchaseRepository)
    {
        $this->middleware(['check_permission:access_current-accounts-suppliers-purs'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
                'accountPaymentsDatatable'
            ]
        );

        $this->middleware(['check_permission:access_delete_current-accounts-suppliers-purs'])->only(
            [
                'destroy',
                'deleteMultiple'
            ]
        );

        $this->currentAccountSupplierPurchaseRepository = $currentAccountSupplierPurchaseRepository;
    }

    /**
     * Muestra una lista de todas las cuentas corrientes filtradas por tipo de transacción.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {

        $currentAccounts = $this->currentAccountSupplierPurchaseRepository->getAllCurrentAccounts();
        $paymentMethods = $this->currentAccountSupplierPurchaseRepository->getPaymentMethods();

        $mergeData = array_merge($currentAccounts, compact('paymentMethods'));
        return view('current-accounts.suppliers.index', $mergeData);
    }

    /**
     * Muestra el formulario para crear una nueva cuenta corriente.
     *
     * @return View
     */
    public function create(): View
    {
        $paymentMethods = $this->currentAccountSupplierPurchaseRepository->getPaymentMethods();
        $suppliers = $this->currentAccountSupplierPurchaseRepository->getSuppliers();
        $currentAccountSettings = $this->currentAccountSupplierPurchaseRepository->getCurrentAccountSettings();
        $currencies = $this->currentAccountSupplierPurchaseRepository->getCurrency();
        return view('current-accounts.suppliers.add-supplier', compact('paymentMethods', 'suppliers', 'currentAccountSettings', 'currencies'));
    }

    /**
     * Almacena una nueva cuenta corriente en la base de datos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(StoreCurrentAccountSupplierRequest $request): JsonResponse
    {
        try {
            $currentAccount = $this->currentAccountSupplierPurchaseRepository->store($request->validated());
            return response()->json($currentAccount);
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar la cuenta corriente.'], 400);
        }
    }

    /**
     * Muestra una cuenta corriente específica.
     *
     * @param int $id
     * @return View
     */
    // public function show(int $id): View
    // {
    //     $currentAccount = $this->currentAccountSupplierPurchaseRepository->getCurrentAccountById($id);
    //     $payments = $currentAccount->payments;

    //     return view('current-accounts.suppliers.details-supplier', compact('currentAccount', 'payments'));
    // }

    /**
     * Devuelve datos para una cuenta corriente específica.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): View
    {
        try {
            $currentAccount = $this->currentAccountSupplierPurchaseRepository->getCurrentAccountById($id);
            $currencies = $this->currentAccountSupplierPurchaseRepository->getCurrency();
            $suppliers = $this->currentAccountSupplierPurchaseRepository->getSuppliers();
            $currentAccountSettings = $this->currentAccountSupplierPurchaseRepository->getCurrentAccountSettings();
            $paymentMethods = $this->currentAccountSupplierPurchaseRepository->getPaymentMethods();
            $currentAccountStatus = $this->currentAccountSupplierPurchaseRepository->getCurrentAccountStatus();
            // return response()->json($currentAccount);
            return view('current-accounts.suppliers.edit-supplier', compact('currentAccount', 'currencies', 'suppliers', 'currentAccountSettings', 'paymentMethods', 'currentAccountStatus'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos de la cuenta corriente.'], 400);
        }
    }

    /**
     * Actualiza una cuenta corriente específica.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCurrentAccountSupplierRequest $request, int $id): JsonResponse
    {
        try {
            $currentAccount = $this->currentAccountSupplierPurchaseRepository->update($id, $request->validated());
            return response()->json($currentAccount);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar la cuenta corriente.'], 400);
        }
    }

    /**
     * Eliminar una cuenta corriente específica.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->currentAccountSupplierPurchaseRepository->destroy($id);
            return response()->json(['success' => true, 'message' => 'Cuenta corriente eliminada correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la cuenta corriente.'], 400);
        }
    }

    /**
     * Elimina varias cuentas corrientes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->currentAccountSupplierPurchaseRepository->deleteMultiple($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Cuentas corrientes eliminadas correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar las cuentas corrientes.'], 400);
        }
    }

    /**
     * Obtiene las cuentas corrientes para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->currentAccountSupplierPurchaseRepository->getCurrentAccountsForDataTable($request);
    }
}
