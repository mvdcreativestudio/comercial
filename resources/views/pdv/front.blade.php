@extends('layouts.layoutMaster')

@section('title', 'PDV - MVD')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/bootstrap/bootstrap.min.css',
    'resources/assets/vendor/libs/fontawesome/fontawesome.min.css'
])
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/select2/select2.min.js',
    'resources/assets/vendor/libs/bootstrap/bootstrap.bundle.min.js',
    'resources/assets/vendor/libs/fontawesome/fontawesome.min.js',
    'resources/assets/vendor/libs/select2/select2.js',

])

<script>
    window.cashRegisterId = "{{ Session::get('open_cash_register_id') }}";
    window.baseUrl = "{{ url('') }}/";
</script>

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <h2 class="mb-4 text-center text-md-start">Punto de Venta</h2>
    </div>
    <div class="col-12 col-md-8">
      <div class="row d-flex search-bar-section align-items-center p-3 mb-4">
        {{-- Buscador de productos --}}
        <div class="col-12 col-md-4 mb-3 mb-md-0">
          <div class="input-group">
            <input class="form-control" type="search" placeholder="Nombre o código" id="html5-search-input" />
            <button class="btn btn-primary"><i class="bx bx-search-alt"></i></button>
          </div>
        </div>
        {{-- Fin buscador de productos --}}
        <div class="col-12 col-md-4 mb-3 mb-md-0">
          <div class="btn-group w-100">
            <button type="button" class="btn btn-primary dropdown-toggle w-100" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Categorías
            </button>
            <div class="dropdown-menu dropdown-menu-end w-100">
              <form class="p-4" onsubmit="return false">
                {{-- Buscador de categorías --}}
                <div class="mb-3">
                  <label for="categorySearchInput" class="form-label"><h5>Filtrar por categoría</h5></label>
                </div>
                {{-- Opciones de categorías --}}
                <div class="mb-3" id="category-container">
                  {{-- Aquí se cargarán las categorías dinámicamente --}}
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4 d-flex justify-content-end mb-3 mb-md-0">
          <button type="button" id="submit-cerrar-caja" class="btn btn-primary me-2 w-100">Cerrar Caja</button>
          <button id="toggle-view-btn" class="btn btn-light w-100" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="left" data-bs-html="true" title="<span>Ver productos en lista</span>">
            <i class="bx bx-list-ul fs-2"></i>
          </button>
        </div>
      </div>
      <div class="row d-flex flex-wrap" id="products-container">
        {{-- Aquí se cargarán los productos --}}
      </div>
    </div>

    <div class="col-md-4">
      <div id="cart" class="card shadow-sm p-3">
        <div class="text-end">
          <button class="btn btn-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd">Seleccionar cliente</button>
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
        <table class="table table-hover" id="cart">
            <thead>
              <tr>
                <th class="col-4">Producto</th>
                <th class="col-3">Cantidad</th>
                <th class="col-1">Unidad</th>
                <th class="col-1">Total</th>
                <th class="col-1"></th>
              </tr>
            </thead>
            <tbody>
              <!-- Aquí se agregarán los productos del carrito -->
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                <td class="subtotal">$0.00</td>
                <td></td>
              </tr>
              <tr>
                <td colspan="3" class="text-right"><strong>Envío:</strong></td>
                <td>$0.00</td>
                <td></td>
              </tr>
              <tr>
                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                <td class="total">$0.00</td>
                <td></td>
              </tr>
            </tfoot>
          </table>

        <div class="row">
          <div class="col-md-12 mt-2">
            <a href="{{ route('pdv.front2') }}" class="btn btn-primary btn-lg d-grid w-100">Pagar</a>
          </div>
        </div>
      </div>
    </div>
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

<!-- Modal para seleccionar sabores -->
<div class="modal fade" id="flavorModal" tabindex="-1" aria-labelledby="flavorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="flavorModalLabel">Seleccionar Sabores</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div id="flavorsContainer" class="mb-3 col-12">
                <label class="form-label">Sabores disponibles</label>
                <select id="flavorsSelect" class="select2 form-select variationOptions" multiple="multiple" name="flavors[]">
                    <!-- Opciones de sabores serán añadidas dinámicamente -->
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

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/jquery/jquery.min.js',
    'resources/assets/vendor/libs/popper/popper.min.js',
    'resources/assets/vendor/libs/bootstrap/bootstrap.min.js',
    'resources/assets/js/pdv.js'
])
@endsection
