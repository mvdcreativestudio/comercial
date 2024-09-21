@extends('layouts/layoutMaster')

@section('title', 'Stock de Productos')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js'
])

@php
$currencySymbol = $settings->currency_symbol;
@endphp

<script>
  window.currencySymbol = '{{ $currencySymbol }}';
</script>

@endsection

@section('page-script')
@vite([
  'resources/assets/js/app-ecommerce-product-stock-list.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light"></span> Stock de Productos
</h4>

@if(session('success'))
  <div class="alert alert-success d-flex" role="alert">
    <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i class="bx bx-user fs-6"></i></span>
    <div class="d-flex flex-column ps-1">
      <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Correcto!</h6>
      <span>{{ session('success') }}</span>
    </div>
  </div>
@elseif(session('error'))
  <div class="alert alert-danger d-flex" role="alert">
    <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i class="bx bx-user fs-6"></i></span>
    <div class="d-flex flex-column ps-1">
      <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error!</h6>
      <span>{{ session('error') }}</span>
    </div>
  </div>
@endif

<!-- Ver / Ocultar columnas de la tabla -->
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between">
    <p class="text-muted small">
      <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
    </p>
  </div>
  <div class="collapse" id="columnSwitches">
    <div class="mt-0 d-flex flex-wrap">
      <div class="mx-0">
        <label class="switch switch-square">
          <input type="checkbox" class="toggle-column switch-input" data-column="0" checked>
          <span class="switch-toggle-slider">
            <span class="switch-on"><i class="bx bx-check"></i></span>
            <span class="switch-off"><i class="bx bx-x"></i></span>
          </span>
          <span class="switch-label">Imagen</span>
        </label>
      </div>
      <div class="mx-3">
        <label class="switch switch-square">
          <input type="checkbox" class="toggle-column switch-input" data-column="1" checked>
          <span class="switch-toggle-slider">
            <span class="switch-on"><i class="bx bx-check"></i></span>
            <span class="switch-off"><i class="bx bx-x"></i></span>
          </span>
          <span class="switch-label">Nombre</span>
        </label>
      </div>
      <div class="mx-3">
        <label class="switch switch-square">
          <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
          <span class="switch-toggle-slider">
            <span class="switch-on"><i class="bx bx-check"></i></span>
            <span class="switch-off"><i class="bx bx-x"></i></span>
          </span>
          <span class="switch-label">Local</span>
        </label>
      </div>
      <div class="mx-3">
        <label class="switch switch-square">
          <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
          <span class="switch-toggle-slider">
            <span class="switch-on"><i class="bx bx-check"></i></span>
            <span class="switch-off"><i class="bx bx-x"></i></span>
          </span>
          <span class="switch-label">Estado</span>
        </label>
      </div>
      <div class="mx-3">
        <label class="switch switch-square">
          <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
          <span class="switch-toggle-slider">
            <span class="switch-on"><i class="bx bx-check"></i></span>
            <span class="switch-off"><i class="bx bx-x"></i></span>
          </span>
          <span class="switch-label">Stock</span>
        </label>
      </div>
    </div>
  </div>
</div>

<!-- Product List Cards -->
<div class="row" id="product-list-container" data-ajax-url="{{ route('products.datatable') }}">
  <!-- Aquí se generarán las tarjetas de productos mediante JS -->
</div>

@endsection
