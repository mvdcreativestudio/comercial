@extends('layouts/layoutMaster')

@section('title', 'Editar Pago de Cuenta Corriente')

@section('page-script')
@vite([
    'resources/assets/js/client-current-account/client-current-account-payment/app-client-current-account-payment-edit.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Pagos de Cuentas Corrientes
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h5 class="card-title">Editar Pago</h5>
    <a href="{{ route('current-account-client-payments.show', $currentAccount->id) }}" class="btn btn-primary">Volver</a>
  </div>
  <div class="card-body">
    <form id="editPaymentForm" method="POST" action="{{ route('current-account-client-payments.update', $payment->id) }}">
      @csrf
      @method('PUT') <!-- Usamos PUT para la edición -->

      <input type="hidden" name="current_account_id" value="{{ $currentAccount->id }}">
      <input type="hidden" name="current_account_payment_id" value="{{ $payment->id }}">
      <!-- Cliente -->
      <div class="mb-3">
        <label for="client_id" class="form-label">Cliente</label>
        <select class="form-select" id="client_id" name="client_id" required disabled>
          <option value="{{ $client->id }}">{{ $client->name }}</option>
          <input type="hidden" name="client_id" value="{{ $client->id }}">
        </select>
      </div>

      <!-- Debe (Monto total de la cuenta corriente) -->
      <div class="mb-3">
        <label for="debe" class="form-label">Debe (Monto Total)</label>
        <input type="text" class="form-control" id="debe" name="debe" value="{{ number_format($currentAccount->total_debit, 2) }}" disabled>
      </div>

      <!-- Haber (Total pagado hasta el momento) -->
      <div class="mb-3">
        <label for="haber" class="form-label">Haber (Total Pagado)</label>
        <input type="text" class="form-control" id="haber" name="haber" value="{{ number_format($currentAccount->payments->sum('payment_amount'), 2) }}" disabled>
      </div>

      <!-- Monto Pagado -->
      <div class="mb-3">
        <label for="payment_amount" class="form-label">Monto Pagado</label>
        <input type="number" class="form-control" id="payment_amount" name="payment_amount" required value="{{ $payment->payment_amount }}" placeholder="Ingrese el monto pagado">
      </div>

      <!-- Método de Pago -->
      <div class="mb-3">
        <label for="payment_method_id" class="form-label">Método de Pago</label>
        <select class="form-select" id="payment_method_id" name="payment_method_id" required>
          <option value="" selected disabled>Seleccione un método de pago</option>
          @foreach($paymentMethods as $method)
          <option value="{{ $method->id }}" @if($method->id == $payment->payment_method_id) selected @endif>{{ $method->description }}</option>
          @endforeach
        </select>
      </div>

      <!-- Fecha de Pago -->
      <div class="mb-3">
        <label for="payment_date" class="form-label">Fecha de Pago</label>
        <input type="date" class="form-control" id="payment_date" name="payment_date" required value="{{ $payment->payment_date->format('Y-m-d') }}">
      </div>

      <!-- Botón para enviar -->
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="submitEditPaymentBtn">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

@endsection
