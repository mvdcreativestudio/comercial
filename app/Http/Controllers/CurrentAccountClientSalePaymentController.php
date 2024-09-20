<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrentAccountPaymentRequest; // Cambiar el request si es necesario
use App\Repositories\CurrentAccountPaymentClientSaleRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrentAccountClientSalePaymentController extends Controller
{
    protected $currentAccountPaymentClientSaleRepository;

    public function __construct(CurrentAccountPaymentClientSaleRepository $currentAccountPaymentClientSaleRepository)
    {
        $this->middleware(['check_permission:access_current-accounts-clients-payments'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
                'accountPaymentsDatatable'
            ]
        );

        $this->middleware(['check_permission:access_delete_current-accounts-clients-payments'])->only(
            [
                'destroy',
                'deleteMultiple'
            ]
        );

        $this->currentAccountPaymentClientSaleRepository = $currentAccountPaymentClientSaleRepository;
    }

    /**
     * Muestra una lista de todos los pagos de cuentas corrientes.
     *
     * @param Request $request
     * @return View
     */
    // public function index(Request $request): View
    // {
        
    // }

    /**
     * Muestra el formulario para crear un nuevo pago de cuenta corriente.
     *
     * @return View
     */
    public function create(): View
    {
        $paymentMethods = $this->currentAccountPaymentClientSaleRepository->getPaymentMethods();
        $clients = $this->currentAccountPaymentClientSaleRepository->getClients();
        $currencies = $this->currentAccountPaymentClientSaleRepository->getCurrency();

        return view('current-accounts.clients.add-client-payment', compact('paymentMethods', 'clients', 'currencies'));
    }

    /**
     * Almacena un nuevo pago de cuenta corriente en la base de datos.
     *
     * @param StoreCurrentAccountPaymentRequest $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $currentAccountPayment = $this->currentAccountPaymentClientSaleRepository->storePayment($request->validated());
            return response()->json($currentAccountPayment);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar el pago de la cuenta corriente.'], 400);
        }
    }

    /**
     * Muestra un pago de cuenta corriente específico.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $currentAccountPayments = $this->currentAccountPaymentClientSaleRepository->getAllCurrentAccountPayments($id);
        $paymentMethods = $this->currentAccountPaymentClientSaleRepository->getPaymentMethods();
        $currencies = $this->currentAccountPaymentClientSaleRepository->getCurrency();
        $currentAccountStatus = $this->currentAccountPaymentClientSaleRepository->getCurrentAccountStatus();

        // dd($currentAccountStatus);
        $mergeData = array_merge($currentAccountPayments, compact('paymentMethods', 'currencies', 'currentAccountStatus'));
        // dd($mergeData);
        return view('current-accounts.clients.current-account-payment.index', $mergeData);
    }

    /**
     * Muestra el formulario para editar un pago de cuenta corriente específico.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $payment = $this->currentAccountPaymentClientSaleRepository->getPaymentById($id);
        $paymentMethods = $this->currentAccountPaymentClientSaleRepository->getPaymentMethods();
        $currencies = $this->currentAccountPaymentClientSaleRepository->getCurrency();

        return view('current-accounts.clients.edit-client-payment', compact('payment', 'paymentMethods', 'currencies'));
    }

    /**
     * Actualiza un pago de cuenta corriente específico.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $payment = $this->currentAccountPaymentClientSaleRepository->updatePayment($id, $request->validated());
            return response()->json($payment);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el pago de la cuenta corriente.'], 400);
        }
    }

    /**
     * Elimina un pago de cuenta corriente específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->currentAccountPaymentClientSaleRepository->destroyPayment($id);
            return response()->json(['success' => true, 'message' => 'Pago de cuenta corriente eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el pago de la cuenta corriente.'], 400);
        }
    }

    /**
     * Elimina múltiples pagos de cuentas corrientes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->currentAccountPaymentClientSaleRepository->deleteMultiple($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Pagos de cuentas corrientes eliminados correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los pagos de cuentas corrientes.'], 400);
        }
    }

    /**
     * Obtiene los pagos de cuentas corrientes para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->currentAccountPaymentClientSaleRepository->getPaymentsForDataTable($request->account_id);
    }
}
