<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrentAccountPaymentRequest; // Cambiar el request si es necesario
use App\Http\Requests\StoreCurrentAccountPaymentSupplierRequest;
use App\Http\Requests\UpdateCurrentAccountPaymentSupplierRequest;
use App\Repositories\CurrentAccountPaymentSupplierPurchaseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CurrentAccountSupplierPurchasePaymentController extends Controller
{
    protected $currentAccountPaymentSupplierPurchaseRepository;

    public function __construct(CurrentAccountPaymentSupplierPurchaseRepository $currentAccountPaymentSupplierPurchaseRepository)
    {
        $this->middleware(['check_permission:access_current-accounts-suppliers-payments'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
                'accountPaymentsDatatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_current-accounts-suppliers-payments'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->currentAccountPaymentSupplierPurchaseRepository = $currentAccountPaymentSupplierPurchaseRepository;
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
    public function create(Request $request, $currentAccountId): View
    {
        $currentAccount = $this->currentAccountPaymentSupplierPurchaseRepository->getCurrentAccount($currentAccountId);
        $paymentMethods = $this->currentAccountPaymentSupplierPurchaseRepository->getPaymentMethods();
        $supplier = $this->currentAccountPaymentSupplierPurchaseRepository->getSupplierByCurrentAccount($currentAccountId);

        return view('current-accounts.suppliers.current-account-payment.add-current-account-payment', compact('paymentMethods', 'supplier', 'currentAccount'));
    }

    /**
     * Almacena un nuevo pago de cuenta corriente en la base de datos.
     *
     * @param StoreCurrentAccountPaymentRequest $request
     * @return JsonResponse
     */
    public function store(StoreCurrentAccountPaymentSupplierRequest $request): JsonResponse
    {
        try {
            $currentAccountPayment = $this->currentAccountPaymentSupplierPurchaseRepository->storePayment($request->validated());
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
        $currentAccount = $this->currentAccountPaymentSupplierPurchaseRepository->getCurrentAccount($id);
        $currentAccountPayments = $this->currentAccountPaymentSupplierPurchaseRepository->getAllCurrentAccountPayments($id);
        $paymentMethods = $this->currentAccountPaymentSupplierPurchaseRepository->getPaymentMethods();
        $currentAccountStatus = $this->currentAccountPaymentSupplierPurchaseRepository->getCurrentAccountStatus();

        $mergeData = array_merge($currentAccountPayments, compact('paymentMethods', 'currentAccountStatus', 'currentAccount'));
        return view('current-accounts.suppliers.current-account-payment.index', $mergeData);
    }

    /**
     * Muestra el formulario para editar un pago de cuenta corriente específico.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $payment = $this->currentAccountPaymentSupplierPurchaseRepository->getPaymentById($id);
        $paymentMethods = $this->currentAccountPaymentSupplierPurchaseRepository->getPaymentMethods();
        $currentAccount = $this->currentAccountPaymentSupplierPurchaseRepository->getCurrentAccount($payment->current_account_id);
        $supplier = $this->currentAccountPaymentSupplierPurchaseRepository->getSupplierByCurrentAccount($payment->current_account_id);

        return view('current-accounts.suppliers.current-account-payment.edit-current-account-payment', compact('payment', 'paymentMethods', 'currentAccount', 'supplier'));
    }

    /**
     * Actualiza un pago de cuenta corriente específico.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCurrentAccountPaymentSupplierRequest $request, int $id): JsonResponse
    {
        try {
            $payment = $this->currentAccountPaymentSupplierPurchaseRepository->updatePayment($id, $request->validated());
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
            $this->currentAccountPaymentSupplierPurchaseRepository->destroyPayment($id);
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
            $this->currentAccountPaymentSupplierPurchaseRepository->deleteMultiple($request->input('ids'));
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
        return $this->currentAccountPaymentSupplierPurchaseRepository->getPaymentsForDataTable($request->account_id);
    }
}
