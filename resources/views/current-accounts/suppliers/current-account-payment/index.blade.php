@extends('layouts/layoutMaster')

@section('title', 'Detalle de Gasto')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'
])
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
<script>
  window.baseUrl = "{{ url('/') }}";
</script>
@endsection

@section('page-script')
@vite([
'resources/assets/js/supplier-current-account/supplier-current-account-payment/app-supplier-current-account-payment-delete.js',
])
@endsection

@section('content')
@php
$statusClass = [
"Paid" => "text-success", // Green color for paid
"Unpaid" => "text-danger", // Red color for unpaid
"Partial" => "text-warning" // Yellow color for partial
];
$status = $currentAccount->status->value;
@endphp
<div class="row">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4>Pagos de Cuenta Corriente
        <span class="text-muted">/ #{{ $currentAccount->id ?? '' }}</span>
      </h4>
      <!-- Botón para abrir modal de agregar pago -->
      <a href="{{ route('current-account-supplier-pays.create', $currentAccount->id) }}"
        class="btn btn-primary">Agregar Pago</a>
    </div>
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div class="mb-3">
          <label for="formFile" class="form-label">Fecha de Creación</label>
          <input class="form-control" type="date" value="{{ $currentAccount->transaction_date->format('Y-m-d') ?? '' }}"
            id="formFile" disabled>
        </div>
        <div class="mb-3 col-4">
          <label for="formFile" class="form-label">Cliente</label>
          <input class="form-control" type="text" value="{{ $currentAccount->supplier->name ?? 'N/A' }}" disabled>
        </div>
        <div class="mb-3 col-2">
          <label for="formFile" class="form-label">Moneda</label>
          <input class="form-control" type="text" value="{{ $currentAccount->currency->name ?? 'N/A' }}" disabled>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <strong>Estado de la Cuenta: </strong>
        <span class="ms-2 {{ $statusClass[$status] ?? 'text-muted' }}">
          {{ $currentAccountStatus[$status] ?? 'N/A' }}
        </span>
      </div>
    </div>


    <div class="card-datatable table-responsive">
      <table class="dt-responsive table border-top datatables-current-account-payments-supplier-pays">
        <thead class="text-center table-dark">
          <tr>
            <th class="font-white">Método de Pago</th>
            <th class="font-white">Debe</th>
            <th class="font-white">Haber</th>
            <th class="font-white">Moneda</th>
            <th class="font-white">Fecha de Pago</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody class="text-center">
          <!-- Primer row, valor del crédito (Debe) -->
          <tr>
            <th>{{ 'Crédito Inicial' }}</th>
            <th>{{ number_format($currentAccount->total_debit, 2) }}</th>
            <th class="bg-gray2"></th> <!-- El valor de "haber" para el crédito inicial es vacío -->
            <th>{{ $currentAccount->currency->name ?? 'N/A' }}</th>
            <th>{{ '-'}}</th>
            <th></th>
          </tr>
          @if($currentAccountPayments->isEmpty())
            <div class="alert alert-warning text-center">
              No hay pagos registrados para esta cuenta corriente.
            </div>
          @else
            <!-- Los pagos, con valores en la columna "Haber" -->
            @foreach($currentAccountPayments as $payment)
            <tr>
              <th>{{ $payment->paymentMethod->description ?? 'N/A' }}</th>
              <th class="bg-gray2"></th> <!-- El valor de "debe" para los pagos es vacío -->
              <th>{{ number_format($payment->payment_amount, 2) }}</th>
              <th>{{ $payment->currentAccount->currency->name ?? 'N/A' }}</th>
              <th>{{ $payment->payment_date->format('d/m/Y') }}</th>
              <th>
                <a href="{{ route('current-account-supplier-pays.edit', $payment->id) }}"
                  class="btn btn-sm btn-warning">Editar</a>
                <a class="btn btn-sm btn-danger delete-record text-white" data-id="{{ $payment->id }}">Eliminar</a>
              </th>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="text-center">
            <tr>
              <th class="font-white table-dark">Total Debe</th>
              <th class="font-white table-dark">{{ number_format($currentAccount->total_debit - $currentAccountPayments->sum('payment_amount'), 2)  }}
              <th colspan="3" class="bg-gray2"></th>
            </tr>
          </tfoot>
          <tfoot class="text-center">
            <tr>
              <th class="font-white table-dark">Total Pagado</th>
              <th class="font-white table-dark">{{ number_format($currentAccountPayments->sum('payment_amount'), 2) }}
              </th>
              <th colspan="3" class="bg-gray2"></th>
            </tr>
          </tfoot>
        @endif
      </table>
    </div>

    <div class="d-flex justify-content-end m-2">
      <a href="{{ route('current-account-supplier-purs.index') }}" class="btn btn-secondary">Volver</a>
    </div>
  </div>
</div>
@endsection

{{-- @include('content.accounting.expenses.expenses-payments-methods.add-expense-payment-method')
@include('content.accounting.expenses.expenses-payments-methods.edit-expense-payment-method') --}}
{{-- @include('content.accounting.expenses.details-expense') --}}