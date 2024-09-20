<?php

namespace App\Repositories;

use App\Enums\CurrentAccounts\StatusPaymentEnum;
use App\Models\Client;
use App\Models\Currency;
use App\Models\CurrentAccount;
use App\Models\CurrentAccountPayment;
use App\Models\CurrentAccountSettings;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CurrentAccountPaymentClientSaleRepository
{
    /**
     * Obtiene todos los pagos de cuentas corrientes filtrados por tipo de transacción.
     *
     * @param int $id
     * @return array
     */
    public function getAllCurrentAccountPayments(int $id): array
    {
        // Obtiene los pagos de las cuentas corrientes
        $currentAccountPayments = CurrentAccountPayment::with(['currentAccount.client', 'currentAccount.currency'])
            ->where('current_account_id', $id)
            ->get();

        $currentAccount = CurrentAccount::find($id);

            // dd($currentAccountPayments);
        return compact('currentAccountPayments', 'currentAccount');
    }

    /**
     * Almacena un nuevo pago de cuenta corriente en la base de datos.
     *
     * @param array $data
     * @return CurrentAccountPayment
     */
    public function storePayment(array $data): CurrentAccountPayment
    {
        DB::beginTransaction();

        try {
            // Crear el pago de la cuenta corriente
            $currentAccountPayment = CurrentAccountPayment::create([
                'current_account_id' => $data['current_account_id'],
                'payment_amount' => $data['payment_amount'],
                'payment_date' => $data['payment_date'],
                'payment_method_id' => $data['payment_method_id'],
                'currency_id' => $data['currency_id'],
            ]);

            // Actualizar el estado de la cuenta corriente (pagada o parcial)
            $currentAccount = CurrentAccount::find($data['current_account_id']);
            $this->updateCurrentAccountStatus($currentAccount);

            DB::commit();

            return $currentAccountPayment;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al crear el pago de la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza un pago de cuenta corriente específico.
     *
     * @param int $paymentId
     * @param array $data
     * @return CurrentAccountPayment
     */
    public function updatePayment(int $paymentId, array $data): CurrentAccountPayment
    {
        DB::beginTransaction();

        try {
            $payment = CurrentAccountPayment::findOrFail($paymentId);
            $payment->update([
                'payment_amount' => $data['payment_amount'],
                'payment_date' => $data['payment_date'],
                'payment_method_id' => $data['payment_method_id'],
                'currency_id' => $data['currency_id'],
            ]);

            // Actualizar el estado de la cuenta corriente
            $this->updateCurrentAccountStatus($payment->currentAccount);

            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al actualizar el pago de la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un pago de cuenta corriente específico.
     *
     * @param int $paymentId
     * @return bool
     */
    public function destroyPayment(int $paymentId): bool
    {
        DB::beginTransaction();

        try {
            $payment = CurrentAccountPayment::findOrFail($paymentId);
            $currentAccount = $payment->currentAccount;

            $payment->delete();

            // Actualizar el estado de la cuenta corriente
            $this->updateCurrentAccountStatus($currentAccount);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al eliminar el pago de la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Elimina múltiples pagos de cuentas corrientes.
     *
     * @param array $ids
     * @return void
     */

    public function deleteMultiple(array $ids): void
    {
        DB::beginTransaction();

        try {
            $payments = CurrentAccountPayment::whereIn('id', $ids)->get();
            $currentAccount = $payments->first()->currentAccount;

            foreach ($payments as $payment) {
                $payment->delete();
            }

            // Actualizar el estado de la cuenta corriente
            $this->updateCurrentAccountStatus($currentAccount);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al eliminar los pagos de la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los pagos de una cuenta corriente para la DataTable.
     *
     * @param int $accountId
     * @return mixed
     */
    public function getPaymentsForDataTable(int $accountId): mixed
    {
        $query = CurrentAccountPayment::where('current_account_id', $accountId)
            ->select(['payment_amount', 'payment_date', 'payment_method_id', 'currency_id']);

        return datatables()->eloquent($query)->make(true);
    }

    /**
     * Actualiza el estado de la cuenta corriente en función de los pagos.
     *
     * @param CurrentAccount $currentAccount
     * @return void
     */
    private function updateCurrentAccountStatus(CurrentAccount $currentAccount): void
    {
        $totalPaid = $currentAccount->payments->sum('payment_amount');

        if ($totalPaid >= $currentAccount->total_debit) {
            $currentAccount->status = 'Paid';
        } elseif ($totalPaid > 0) {
            $currentAccount->status = 'Partial';
        } else {
            $currentAccount->status = 'Unpaid';
        }

        $currentAccount->save();
    }

    /**
     * Obtiene un pago de cuenta corriente por su ID.
     *
     * @param int $paymentId
     * @return CurrentAccountPayment
     */
    public function getPaymentById(int $paymentId): CurrentAccountPayment
    {
        return CurrentAccountPayment::findOrFail($paymentId);
    }

    /**
     * Obtiene todos los métodos de pago.
     *
     * @return Collection
     */
    public function getPaymentMethods()
    {
        return PaymentMethod::all();
    }

    /**
     * Obtiene las configuraciones de cuentas corrientes.
     *
     * @return Collection
     */
    public function getCurrentAccountSettings()
    {
        return CurrentAccountSettings::all();
    }

    /**
     * Obtiene todas las monedas.
     *
     * @return Collection
     */
    public function getCurrency()
    {
        return Currency::all();
    }

    /**
     * Obtiene todos los clientes.
     *
     * @return Collection
     */
    public function getClients()
    {
        return Client::all();
    }

    public function getCurrentAccountStatus()
    {
        return StatusPaymentEnum::getTranslateds();
    }
}
