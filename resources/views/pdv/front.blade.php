@extends('layouts.layoutMaster')

@section('title', 'PDV - MVD')

@section('vendor-style')
@vite([

'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
// 'resources/assets/vendor/libs/bootstrap/bootstrap.min.css',
// 'resources/assets/vendor/libs/fontawesome/fontawesome.min.css'
])

<style>
  .modal-bottom {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 50%; /* Ajusta la altura según sea necesario */
  display: flex;
  justify-content: center;
  align-items: flex-end;
  margin: 0;
}

</style>
@endsection

@section('vendor-script')
@vite([

// 'resources/assets/vendor/libs/select2/select2.min.js',
// 'resources/assets/vendor/libs/bootstrap/bootstrap.bundle.min.js',
// 'resources/assets/vendor/libs/fontawesome/fontawesome.min.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/js/pdv.js'
])

<script>
  window.cashRegisterId = "{{ Session::get('open_cash_register_id') }}";
    window.baseUrl = "{{ url('') }}/";
</script>

@php
$openCashRegister = Session::get('open_cash_register_id');
@endphp
@if ($openCashRegister !== null)

@section('content')
<div class="container-fluid">
  <div id="errorContainer" class="alert alert-danger d-none" role="alert"></div>
  <div class="row">
    <div class="col-12">
      <h2 class="mb-4 text-center text-md-start">Punto de Venta</h2>
    </div>

    <div class="col-12">
      <div class="row d-flex align-items-center p-3 mb-4 card sticky-top">
        {{-- Buscador de productos --}}
        <div class="col-12 mb-3">
          <div class="input-group">
            <input class="form-control" type="search" placeholder="Buscar por nombre o código" id="html5-search-input" />
            <button class="btn btn-primary"><i class="bx bx-search-alt"></i></button>
          </div>
        </div>
        {{-- Fin buscador de productos --}}
        {{-- Botones de acciones --}}
        <div class="col-12 d-flex justify-content-end align-items-center">
          {{-- Botón de categorías --}}
          <div class="btn-group ms-2">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-category"></i> Categorías
            </button>
            <div class="dropdown-menu dropdown-menu-end w-auto" style="min-width: 300px;">
              <form class="p-3" onsubmit="return false">
                <div class="mb-2">
                  <h6 class="mb-1">Filtrar por categoría</h6>
                  {{-- Input para buscar categoría --}}
                </div>
                <div class="mb-2" id="category-container">
                  {{-- Aquí se cargarán las categorías dinámicamente --}}
                </div>
              </form>
            </div>
          </div>
          {{-- Botón para cerrar caja --}}
          <button type="button" id="submit-cerrar-caja" class="btn btn-outline-danger d-flex align-items-center ms-2">
            <i class="bx bx-lock-alt me-2"></i> Cerrar Caja
          </button>

          {{-- Botón de cambio de vista --}}
          <button id="toggle-view-btn" class="btn btn-outline-secondary d-flex align-items-center ms-2" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="left" data-bs-html="true" title="Ver productos en lista">
            <i class="bx bx-list-ul fs-5"></i>
          </button>

          {{-- Botón para ver carrito --}}
          <button id="view-cart-btn" class="btn btn-outline-secondary d-flex align-items-center ms-2 position-relative" data-bs-toggle="modal" data-bs-target="#cartModal">
            <i class="bx bx-cart fs-5"></i>
            <span id="cart-count" class="badge bg-danger position-absolute top-0 start-100 translate-middle">0</span>
          </button>
        </div>
      </div>

      {{-- Contenedor de productos --}}
      <div class="row d-flex flex-wrap" id="products-container">
        {{-- Aquí se cargarán los productos --}}
      </div>
    </div>
  </div>
</div>


<!-- Modal para ver el carrito -->
<div class="modal modal-bottom fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="cartModalLabel">🛒 Carrito de Compras</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Contenedor dinámico de productos del carrito -->
        <div id="cart-items">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Producto</th>
                <th class="text-center">Cantidad</th>
                <th class="text-center">Precio</th>
                <th class="text-center">Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="cart-items-body">
              <!-- Aquí se agregarán los productos del carrito -->
            </tbody>
          </table>
        </div>
        <!-- Totales -->
        <div class="mt-3 col-4 ms-auto">
          <div class="justify-content-between mb-1">
            <span><strong>Subtotal:</strong> <span class="subtotal">$191.440,00</span></span>
          </div>
          <div class="justify-content-between mb-1">
            <span><strong>Envío:</strong> <span>$0</span></span>
          </div>
          <div class="justify-content-between mb-1 border-top pt-2">
            <span><strong>Total:</strong> <span class="total">$191.440,00</span></span>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        <a href="{{ route('pdv.front2') }}" class="btn btn-primary">Pagar</a>
      </div>
    </div>
  </div>
</div>




<!-- Offcanvas Crear Cliente -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="crearClienteOffcanvas"
  aria-labelledby="crearClienteOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="crearClienteOffcanvasLabel" class="offcanvas-title">Crear Cliente</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="formCrearCliente">
      <div class="mb-3">
        <label for="nombreCliente" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombreCliente" required>
      </div>
      <div class="mb-3">
        <label for="apellidoCliente" class="form-label">Apellido</label>
        <input type="text" class="form-control" id="apellidoCliente" required>
      </div>
      <div class="mb-3">
        <label for="tipoCliente" class="form-label">Tipo de Cliente</label>
        <select class="form-select" id="tipoCliente" required>
          <option value="individual">Individual</option>
          <option value="company">Compañía</option>
        </select>
      </div>
      <div class="mb-3" id="ciField">
        <label for="ciCliente" class="form-label">CI</label>
        <input type="text" class="form-control" id="ciCliente">
      </div>
      <div class="mb-3" id="rutField" style="display: none;">
        <label for="rutCliente" class="form-label">RUT</label>
        <input type="text" class="form-control" id="rutCliente">
      </div>
      <div class="mb-3">
        <label for="emailCliente" class="form-label">Correo Electrónico</label>
        <input type="email" class="form-control" id="emailCliente" required>
      </div>
      <button type="button" class="btn btn-primary" id="guardarCliente">Guardar</button>
    </form>
  </div>
</div>

<!-- Modal para seleccionar variaciones -->
<div class="modal fade" id="flavorModal" tabindex="-1" aria-labelledby="flavorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="flavorModalLabel">Seleccionar Variaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="flavorsContainer" class="mb-3 col-12">
          <label class="form-label">Variaciones disponibles</label>
          <select id="flavorsSelect" class="select2 form-select variationOptions" multiple="multiple" name="flavors[]">
            <!-- Opciones de variaciones serán añadidas dinámicamente -->
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="saveFlavors" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </div>
</div>
@endsection
@else

@section('content')

<div class="alert alert-success mt-3 mb-3">
  <h4 class="alert-heading">¡Caja cerrada!</h4>
  <p>Para abrir una nueva caja, haga clic en el botón de abajo.</p>
  <a href="/admin/points-of-sales" class="btn btn-primary">Abrir caja</a>
</div>

@endsection

@endif

