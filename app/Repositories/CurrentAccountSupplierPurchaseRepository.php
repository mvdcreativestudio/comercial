<?php

namespace App\Repositories;

use App\Enums\CurrentAccounts\StatusPaymentEnum;
use App\Enums\CurrentAccounts\TransactionTypeEnum;
use App\Helpers\Helpers;
use App\Models\Currency;
use App\Models\CurrentAccount;
use App\Models\CurrentAccountPayment;
use App\Models\CurrentAccountSettings;
use App\Models\PaymentMethod;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CurrentAccountSupplierPurchaseRepository
{
    /**
     * Obtiene todas las cuentas corrientes filtradas por tipo de transacción.
     *
     * @param string $transactionType
     * @return Collection
     */
    public function getAllCurrentAccounts(): array
    {

        $currentAccounts = CurrentAccount::with(['client', 'supplier', 'currency', 'payments'])->where('transaction_type', TransactionTypeEnum::PURCHASE)->get();

        $totalAmount = $currentAccounts->sum('total_debit');

        $paidAccounts = CurrentAccount::where('status', StatusPaymentEnum::PAID)->where('transaction_type', TransactionTypeEnum::PURCHASE)->count();

        $partialAccounts = CurrentAccount::where('status', StatusPaymentEnum::PARTIAL)->where('transaction_type', TransactionTypeEnum::PURCHASE)->count();

        $unpaidAccounts = CurrentAccount::where('status', StatusPaymentEnum::UNPAID)->where('transaction_type', TransactionTypeEnum::PURCHASE)->count();

        return compact('currentAccounts', 'totalAmount', 'paidAccounts', 'partialAccounts', 'unpaidAccounts');
    }

    /**
     * Obtiene una cuenta corriente por su ID.
     *
     * @param int $id
     * @return CurrentAccount
     */
    public function getCurrentAccountById(int $id): CurrentAccount
    {
        return CurrentAccount::with(['client', 'supplier', 'currency', 'payments'])->findOrFail($id);
    }

    /**
     * Almacena una nueva cuenta corriente en la base de datos.
     *
     * @param array $data
     * @return CurrentAccount
     */
    public function store(array $data): CurrentAccount
    {
        // Comenzamos la transacción para asegurar atomicidad
        DB::beginTransaction();

        try {
            $currentAccountSettings = CurrentAccountSettings::findOrFail($data['current_account_settings_id']);

            $transactionDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime($transactionDate . ' + ' . $currentAccountSettings->payment_terms . ' days'));
            // Crear la nueva cuenta corriente
            $currentAccount = CurrentAccount::create([
                'voucher' => '',
                'total_debit' => $data['amount'],
                'transaction_date' => date('Y-m-d'),
                'due_date' => $dueDate,
                'supplier_id' => $data['supplier_id'],
                'current_account_settings_id' => $data['current_account_settings_id'],
                'currency_id' => $data['currency_id_current_account'],
                'transaction_type' => TransactionTypeEnum::PURCHASE,
                // 'is_paid' => $data['is_paid'],
            ]);

            // Si existe partial_payment, es un pago parcial

            if ($data['partial_payment']) {
                $currentAccount->status = StatusPaymentEnum::PARTIAL;
                $currentAccount->save();

                CurrentAccountPayment::create([
                    'current_account_id' => $currentAccount->id,
                    'payment_amount' => $data['amount_paid'],
                    'payment_date' => date('Y-m-d'),
                    'payment_method_id' => $data['payment_method_id'],
                ]);
            } else {
                $currentAccount->status = StatusPaymentEnum::UNPAID;
                $currentAccount->save();
            }

            // Confirmar la transacción si todo va bien
            DB::commit();

            return $currentAccount;

        } catch (\Exception $e) {
            // Si ocurre algún error, revertir la transacción
            DB::rollBack();
            throw new \Exception('Error al crear la cuenta corriente: ' . $e->getMessage());
        }
    }
    /**
     * Actualiza una cuenta corriente existente.
     *
     * @param int $id
     * @param array $data
     * @return CurrentAccount
     */
    public function update(int $id, array $data): CurrentAccount
    {
        // Comenzamos la transacción para asegurar atomicidad
        DB::beginTransaction();

        try {
            // Buscar la cuenta corriente existente
            $currentAccount = CurrentAccount::findOrFail($id);

            // Obtener las configuraciones de la cuenta corriente
            $currentAccountSettings = CurrentAccountSettings::findOrFail($data['current_account_settings_id']);

            // Calcular la nueva fecha de vencimiento
            $transactionDate = $currentAccount->transaction_date ?? date('Y-m-d'); // Si ya tiene una fecha, conservarla
            $dueDate = date('Y-m-d', strtotime($transactionDate . ' + ' . $currentAccountSettings->payment_terms . ' days'));

            // Actualizar los datos de la cuenta corriente
            $currentAccount->update([
                'total_debit' => $data['amount'],
                'due_date' => $dueDate,
                'supplier_id' => $data['supplier_id'],
                'current_account_settings_id' => $data['current_account_settings_id'],
                'currency_id' => $data['currency_id_current_account'],
            ]);

            // Si la cuenta tiene pagos parciales o es un pago completo
            if ($data['partial_payment']) {
                $currentAccount->status = StatusPaymentEnum::PARTIAL;
                $currentAccount->save();

                // Verificar si ya existen pagos parciales para esta cuenta
                $existingPayments = CurrentAccountPayment::where('current_account_id', $currentAccount->id)->count();

                if ($existingPayments === 0) {
                    // Si no hay pagos parciales previos, crear un nuevo pago
                    CurrentAccountPayment::create([
                        'current_account_id' => $currentAccount->id,
                        'payment_amount' => $data['amount_paid'],
                        'payment_date' => date('Y-m-d'),
                        'payment_method_id' => $data['payment_method_id'],
                    ]);
                } else {
                    // Si ya existen pagos parciales, actualizarlos
                    $payment = CurrentAccountPayment::where('current_account_id', $currentAccount->id)->first();
                    $payment->update([
                        'payment_amount' => $data['amount_paid'],
                        'payment_method_id' => $data['payment_method_id'],
                    ]);
                }
            } else {
                // Si no hay pagos parciales, marcar la cuenta como no pagada y borrar los pagos existentes
                CurrentAccountPayment::where('current_account_id', $currentAccount->id)->delete();

                $currentAccount->status = StatusPaymentEnum::UNPAID;
                $currentAccount->save();
            }

            // Confirmar la transacción si todo va bien
            DB::commit();

            return $currentAccount;

        } catch (\Exception $e) {
            // Si ocurre algún error, revertir la transacción
            DB::rollBack();
            throw new \Exception('Error al actualizar la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una cuenta corriente específica.
     *
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        $currentAccount = $this->getCurrentAccountById($id);
        return $currentAccount->delete();
    }

    /**
     * Elimina múltiples cuentas corrientes.
     *
     * @param array $ids
     * @return int
     */
    public function deleteMultiple(array $ids): int
    {
        return CurrentAccount::whereIn('id', $ids)->delete();
    }

    /**
     * Obtiene las cuentas corrientes para la DataTable.
     *
     * @param Request $request
     * @return mixed
     */
    public function getCurrentAccountsForDataTable($request): mixed
    {
        $query = CurrentAccount::with(['supplier', 'currency'])
            ->select([
                'current_accounts.id',
                'current_accounts.voucher',
                'current_accounts.total_debit',
                'current_accounts.transaction_type',
                'current_accounts.transaction_date',
                'current_accounts.due_date',
                'suppliers.name as supplier_name',
                'currencies.code as currency_code',
                'current_accounts.status',
            ])
            ->leftJoin('suppliers', 'current_accounts.supplier_id', '=', 'suppliers.id')
            ->leftJoin('currencies', 'current_accounts.currency_id', '=', 'currencies.id')
            ->where('current_accounts.transaction_type', TransactionTypeEnum::PURCHASE);

        // Filtrar por rango de fechas
        if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('current_accounts.transaction_date', [$startDate, $endDate]);
        }

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }

    /**
     * Obtiene los pagos de una cuenta corriente para la DataTable.
     *
     * @param int $accountId
     * @return mixed
     */
    // public function getAccountPaymentsForDataTable(int $accountId): mixed
    // {
    //     $query = CurrentAccountPayment::where('current_account_id', $accountId)
    //         ->select(['payment_amount', 'payment_date', 'payment_method_id']);

    //     return datatables()->eloquent($query)->make(true);
    // }

    /**
     * Actualiza el estado de pago de una cuenta corriente.
     *
     * @param int $accountId
     * @param string $paymentStatus
     * @return CurrentAccount
     */
    public function updatePaymentStatus(int $accountId, string $paymentStatus): CurrentAccount
    {
        $currentAccount = $this->getCurrentAccountById($accountId);
        $currentAccount->status = $paymentStatus;
        $currentAccount->save();

        return $currentAccount;
    }

    /**
     * Obtiene todos los metodos de pago.
     *
     * @return Collection
     */
    public function getPaymentMethods(): Collection
    {
        return PaymentMethod::all();
    }

    /**
     * Obtiene el tipo de transacción.
     *
     * @return Collection
     */

    public function getTransactionType(): TransactionTypeEnum
    {
        return TransactionTypeEnum::PURCHASE;
    }
    /**
     * Obtiene los proveedores.
     *
     * @return Collection
     */

    public function getSuppliers(): Collection
    {
        return Supplier::all();
    }

    public function getCurrentAccountSettings()
    {
        return CurrentAccountSettings::where('transaction_type', TransactionTypeEnum::PURCHASE)->get();
    }

    public function getCurrency()
    {
        return Currency::all();
    }

    public function getCurrentAccountStatus()
    {
        return StatusPaymentEnum::cases();
    }
}
