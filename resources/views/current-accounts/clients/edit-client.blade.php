@extends('layouts/layoutMaster')

@section('title', 'Editar Cuenta Corriente de Cliente')

@section('page-script')
@vite([
'resources/assets/js/client-current-account/app-client-current-account-edit.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Contabilidad /</span> Cuentas Corrientes de Clientes
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h5 class="card-title">Editar Cuenta Corriente de Cliente</h5>
    <a href="{{ route('current-account-client-sales.index') }}" class="btn btn-secondary">Volver</a>
  </div>
  <div class="card-body">
    <form id="editCurrentAccountForm" method="POST">
      @csrf
      @method('PUT')
      <!-- Utilizamos el método PUT para la edición -->

      <div class="mb-3">
        <label for="amount" class="form-label">Monto Total</label>
        <input type="number" class="form-control" id="amountEdit" name="amount"
          value="{{ $currentAccount->total_debit }}" required placeholder="Ingrese el monto total de la cuenta">
      </div>

      <div class="mb-3">
        <label for="currency_id_current_account" class="form-label">Moneda</label>
        <select class="form-select" id="currency_id_current_account_edit" name="currency_id_current_account" required>
          <option value="" selected disabled>Seleccione una moneda</option>
          @foreach($currencies as $currency)
          <option value="{{ $currency->id }}" {{ $currency->id == $currentAccount->currency_id ? 'selected' : '' }}>
            {{ $currency->name }}
          </option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label for="client_id" class="form-label">Cliente</label>
        <select class="form-select" id="client_id_edit" name="client_id" required>
          <option value="" selected disabled>Seleccione un cliente</option>
          @foreach($clients as $client)
          <option value="{{ $client->id }}" {{ $client->id == $currentAccount->client_id ? 'selected' : '' }}>
            {{ $client->name }}
          </option>
          @endforeach
        </select>
      </div>

      {{-- Select para tipo de crédito de currentAccountSettings --}}
      <div class="mb-3">
        <label for="current_account_settings_id" class="form-label">Tipo de Crédito</label>
        <select class="form-select" id="current_account_settings_id_edit" name="current_account_settings_id" required>
          <option value="" selected disabled>Seleccione el tipo de crédito</option>
          @foreach($currentAccountSettings as $setting)
          <option value="{{ $setting->id }}" {{ $setting->id == $currentAccount->current_account_settings_id ?
            'selected' : '' }}>
            {{ $setting->payment_terms }}
          </option>
          @endforeach
        </select>
      </div>
      @php
      $statusIsPartial = $currentAccount->status->value == $currentAccountStatus[2]->value;
      @endphp
      {{-- Checkbox para pagos parciales --}}
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="partial_payment_edit" name="partial_payment" {{
          $statusIsPartial ? 'checked' : '' }}>
        <label class="form-check-label" for="partial_payment">¿Pago Parcial?</label>
      </div>

      {{-- Campos adicionales de pago parcial que se muestran si el checkbox está seleccionado --}}
      <div id="partialPaymentFieldsEdit" class="{{ $statusIsPartial ? '' : 'd-none' }}">
        <div class="mb-3">
          <label for="amount_paid" class="form-label">Monto Pagado</label>
          <input type="number" class="form-control" id="amount_paid_edit" name="amount_paid"
            value="{{ $currentAccount->payments->sum('payment_amount') ?? '' }}" placeholder="Ingrese el monto pagado">
        </div>
        <div class="mb-3">
          <label for="payment_method_id" class="form-label">Método de Pago</label>
          <select class="form-select" id="payment_method_id_edit" name="payment_method_id">
            <option value="" selected disabled>Seleccione un método de pago</option>
            @foreach($paymentMethods as $method)
            <option value="{{ $method->id }}" {{ $method->id == $currentAccount->payments->first()?->payment_method_id ?
              'selected' : '' }}>
              {{ $method->description }}
            </option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="updateCurrentAccountBtn"
          data-route="{{ route('current-account-client-sales.update', $currentAccount->id) }}">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

@endsection