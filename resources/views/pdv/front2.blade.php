@extends('layouts.layoutMaster')

@section('title', 'Pago - MVD')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    // 'resources/assets/vendor/libs/bootstrap/bootstrap.min.css',
    // 'resources/assets/vendor/libs/fontawesome/fontawesome.min.css'
])
@endsection

@php
  $currencySymbol = $settings->currency_symbol;
@endphp

<script>
    window.cashRegisterId = "{{ Session::get('open_cash_register_id') }}";
    window.baseUrl = "{{ url('') }}/";
    window.frontRoute = "{{ route('pdv.front') }}";
    // Configuración de las respuestas del POS Scanntech
    const posResponsesConfig = @json(config('posResponses'));
    window.currencySymbol = '{{ $currencySymbol }}';
</script>

@section('content')

<div class="container-fluid p-4">
  <div id="errorContainer" class="alert alert-danger d-none" role="alert"></div>

  <div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0"><button class="btn m-0 p-0"><a href="{{ route('pdv.front') }}"><i class="bx bx-chevron-left fs-2"></i></a></button> Vender</h2>
    </div>


    <div class="col-md-8">
      <div class="row">
        <div class="col-12 mb-3" id="client-selection-container">
          <div class="bg-white d-flex justify-content-between shadow-sm p-3">
            <h5>Cliente</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd"><i class="bx bx-plus"></i></button>
          </div>
        </div>
        <div class="col-12 mb-3">
        <div id="client-info" class="card shadow-sm p-3 mb-3" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Información del Cliente</h5>
                <button id="deselect-client" class="btn-close"></button>
            </div>
            <p><strong>ID:</strong> <span id="client-id"></span></p>
            <p><strong>Nombre:</strong> <span id="client-name"></span></p>
            <p><strong>CI:</strong> <span id="client-ci"></span></p>
            <p><strong>RUT:</strong> <span id="client-rut"></span></p>
        </div>
          <div class="card shadow-sm p-3">
            <h5>Productos comprados</h5>
            <!-- Listado de items seleccionados -->
            <ul class="list-group list-group-flush">
              <!-- Aquí se insertarán los items del carrito dinámicamente -->
            </ul>
          </div>
        </div>
        <div class="col-12">
          <div class="card shadow-sm p-3">
            <h5>Observación</h5>
            <textarea class="form-control" placeholder="Digite la observación aquí"></textarea>
            {{-- Campo para mostrar la nota en el recibo --}}
            {{-- <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="mostrarRecibo">
              <label class="form-check-label" for="mostrarRecibo">Mostrar en el recibo</label>
            </div> --}}
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 mb-3">
        <h5>Resumen del pedido</h5>
        <div class="d-flex justify-content-between">
          <span>Subtotal de productos</span>
          <span class="subtotal">$0.00</span>
        </div>
        <div class="d-flex justify-content-between">
          <span>Descuentos</span>
          <span class="discount-amount">$0.00</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between">
          <strong>Total</strong>
          <strong class="total">$0.00</strong>
        </div>
      </div>

      <div class="discount-section mt-3">
        <div class="card shadow-sm p-3 mb-3 border-0">
            <h5 class="mb-3 font-weight-bold">Descuentos</h5>
            <div class="form-group mb-3">
                <label for="coupon-code" class="text-muted small">Cupón de descuento</label>
                <input type="text" id="coupon-code" class="form-control form-control-sm" placeholder="Ingresa código de cupón">
            </div>
            <div class="form-group mb-3">
                <label for="fixed-discount" class="text-muted small">Descuento fijo</label>
                <div class="input-group">
                    <input type="number" id="fixed-discount" class="form-control form-control-sm" placeholder="Ingresa cantidad o porcentaje">
                    <div class="input-group-append">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-secondary btn-sm">
                                <input type="radio" name="discount-type" value="fixed" autocomplete="off"> Monto
                            </label>
                            <label class="btn btn-outline-secondary btn-sm">
                                <input type="radio" name="discount-type" value="percentage" autocomplete="off"> %
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary btn-sm w-100" id="apply-discount-btn">Aplicar</button>
            <button class="btn btn-danger btn-sm w-100 mt-1" id="quitarDescuento" style="display: none;">Eliminar descuento</button>
        </div>
      </div>

      <div class="card shadow-sm p-3">
        <h5>Seleccione el método de pago</h5>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="cash" checked>
          <label class="form-check-label" for="cash">Efectivo</label>
          <input type="text" id="valorRecibido" class="form-control mt-2 mb-3" placeholder="Valor recibido">
          <p class="text-muted">Vuelto: <span id="vuelto">0</span></p>
          <small id="mensajeError" class="text-danger d-none">El valor recibido es menor al total de la compra.</small>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="debit">
          <label class="form-check-label" for="debit">Débito</label>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="credit">
          <label class="form-check-label" for="credit">Crédito</label>
        </div>
        {{-- <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="other">
          <label class="form-check-label" for="other">Otros</label>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="creditSale">
          <label class="form-check-label" for="creditSale">Venta a crédito</label>
        </div> --}}
      </div>
      <div class="demo-inline-spacing d-flex justify-content-between">
        <a href="{{ route('pdv.front') }}" id="descartarVentaBtn" class="btn btn-light"><i class="bx bx-x"></i>Descartar venta</a>
        <button class="btn btn-secondary"><i class="bx bx-save"></i> Guardar pedido</button>
        <button class="btn btn-success"><i class="bx bx-check"></i> Finalizar venta</button>
      </div>
      <!-- Contenedor para el estado de la transacción -->
      <div id="transaction-status" style="display: none;">
        <div class="spinner-border text-primary" role="status" id="transaction-spinner" style="display: none;">
            <span class="sr-only">Procesando...</span>
        </div>
        <div id="transaction-message" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<!-- Offcanvas Seleccionar Cliente -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasEndLabel" class="offcanvas-title">Seleccionar Cliente</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                <!-- Contenido del off-canvas -->
                <p class="text-center">Selecciona un cliente o crea uno nuevo.</p>
                <button type="button" class="btn btn-primary mb-2 d-grid w-100" data-bs-toggle="offcanvas" data-bs-target="#crearClienteOffcanvas">Crear Cliente</button>
                <!-- Contenedor de la barra de búsqueda -->
                <div class="mb-3" id="search-client-container" style="display: none;">
                    <input type="search" class="form-control" id="search-client" placeholder="Buscar por nombre o CI/RUT">
                </div>
                <!-- Lista de clientes -->
                <ul id="client-list" class="list-group">
                    <!-- Aquí se cargarán los clientes -->
                </ul>
            </div>
        </div>


<!-- Offcanvas Crear Cliente -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="crearClienteOffcanvas" aria-labelledby="crearClienteOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="crearClienteOffcanvasLabel" class="offcanvas-title">Crear Cliente</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="formCrearCliente">
      <div class="mb-3">
        <label for="tipoCliente" class="form-label">Tipo de Cliente</label>
        <select class="form-select" id="tipoCliente" required>
          <option value="individual">Persona</option>
          <option value="company">Empresa</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="nombreCliente" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombreCliente" required>
      </div>
      <div class="mb-3">
        <label for="apellidoCliente" class="form-label">Apellido</label>
        <input type="text" class="form-control" id="apellidoCliente" required>
      </div>
      <div class="mb-3" id="ciField">
        <label for="ciCliente" class="form-label">CI</label>
        <input type="text" class="form-control" id="ciCliente">
      </div>
      <div class="mb-3" id="rutField" style="display: none;">
        <label for="rutCliente" class="form-label">RUT</label>
        <input type="text" class="form-control" id="rutCliente">
      </div>
      <!-- Campo Razón Social -->
      <div class="mb-3" id="razonSocialField" style="display: none;">
        <label for="razonSocialCliente" class="form-label">Razón Social</label>
        <input type="text" class="form-control" id="razonSocialCliente">
      </div>
      <!-- Campo Dirección -->
      <div class="mb-3">
        <label for="direccionCliente" class="form-label">Dirección</label>
        <input type="text" class="form-control" id="direccionCliente" required>
      </div>
      <div class="mb-3">
        <label for="emailCliente" class="form-label">Correo Electrónico</label>
        <input type="email" class="form-control" id="emailCliente" required>
      </div>
      <button type="button" class="btn btn-primary" id="guardarCliente">Guardar</button>
    </form>
  </div>
</div>


@endsection

@section('vendor-script')
@vite([
    // 'resources/assets/vendor/libs/jquery/jquery.min.js',
    // 'resources/assets/vendor/libs/popper/popper.min.js',
    // 'resources/assets/vendor/libs/bootstrap/bootstrap.min.js',
    'resources/assets/js/pdvCheckout.js'
])
@endsection
