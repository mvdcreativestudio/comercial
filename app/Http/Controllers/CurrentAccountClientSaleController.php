<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrentAccountRequest;
use App\Http\Requests\UpdateCurrentAccountRequest;
use App\Repositories\CurrentAccountClientSaleRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrentAccountClientSaleController extends Controller
{
    /**
     * El repositorio para las operaciones de cuentas corrientes.
     *
     * @var CurrentAccountClientSaleRepository
     */
    protected $currentAccountClientSaleRepository;

    /**
     * Inyecta los repositorios en el controlador y los middleware.
     *
     * @param CurrentAccountClientSaleRepository $currentAccountClientSaleRepository
     */
    public function __construct(CurrentAccountClientSaleRepository $currentAccountClientSaleRepository)
    {
        $this->middleware(['check_permission:access_current-accounts-clients-sales'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
                'accountPaymentsDatatable'
            ]
        );

        $this->middleware(['check_permission:access_delete_current-accounts-clients-sales'])->only(
            [
                'destroy',
                'deleteMultiple'
            ]
        );

        $this->currentAccountClientSaleRepository = $currentAccountClientSaleRepository;
    }

    /**
     * Muestra una lista de todas las cuentas corrientes filtradas por tipo de transacción.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {

        $currentAccounts = $this->currentAccountClientSaleRepository->getAllCurrentAccounts();
        $paymentMethods = $this->currentAccountClientSaleRepository->getPaymentMethods();

        $mergeData = array_merge($currentAccounts, compact('paymentMethods'));
        return view('current-accounts.clients.index', $mergeData);
    }

    /**
     * Muestra el formulario para crear una nueva cuenta corriente.
     *
     * @return View
     */
    public function create(): View
    {
        $paymentMethods = $this->currentAccountClientSaleRepository->getPaymentMethods();
        $clients = $this->currentAccountClientSaleRepository->getClients();
        $currentAccountSettings = $this->currentAccountClientSaleRepository->getCurrentAccountSettings();
        $currencies = $this->currentAccountClientSaleRepository->getCurrency();
        return view('current-accounts.clients.add-client', compact('paymentMethods', 'clients', 'currentAccountSettings', 'currencies'));
    }

    /**
     * Almacena una nueva cuenta corriente en la base de datos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(StoreCurrentAccountRequest $request): JsonResponse
    {
        try {
            $currentAccount = $this->currentAccountClientSaleRepository->store($request->validated());
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
    //     $currentAccount = $this->currentAccountClientSaleRepository->getCurrentAccountById($id);
    //     $payments = $currentAccount->payments;

    //     return view('current-accounts.clients.details-client', compact('currentAccount', 'payments'));
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
            $currentAccount = $this->currentAccountClientSaleRepository->getCurrentAccountById($id);
            $currencies = $this->currentAccountClientSaleRepository->getCurrency();
            $clients = $this->currentAccountClientSaleRepository->getClients();
            $currentAccountSettings = $this->currentAccountClientSaleRepository->getCurrentAccountSettings();
            $paymentMethods = $this->currentAccountClientSaleRepository->getPaymentMethods();
            $currentAccountStatus = $this->currentAccountClientSaleRepository->getCurrentAccountStatus();
            // dd($currentAccountStatus);
            // return response()->json($currentAccount);
            return view('current-accounts.clients.edit-client', compact('currentAccount', 'currencies', 'clients', 'currentAccountSettings', 'paymentMethods', 'currentAccountStatus'));
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
    public function update(UpdateCurrentAccountRequest $request, int $id): JsonResponse
    {
        try {
            $currentAccount = $this->currentAccountClientSaleRepository->update($id, $request->validated());
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
            $this->currentAccountClientSaleRepository->destroy($id);
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
            $this->currentAccountClientSaleRepository->deleteMultiple($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Cuentas corrientes eliminadas correctamente.']);
        } catch (\Exception $e) {
            dd($e->getMessage());
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
        return $this->currentAccountClientSaleRepository->getCurrentAccountsForDataTable($request);
    }
}
