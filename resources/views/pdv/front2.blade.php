@extends('layouts.layoutMaster')

@section('title', 'Pago - MVD')

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

@section('content')
<div class="container-fluid p-4">
  <div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0"><button class="btn m-0 p-0"><a href="{{ route('pdv.front') }}"><i class="bx bx-chevron-left fs-2"></i></a></button> Vender <span class="text-secondary fs-4">$1,300.00</span></h2>
    </div>

    <div class="col-md-8">
      <div class="row">
        <div class="col-12 mb-3">
          <div class="bg-white d-flex justify-content-between shadow-sm p-3">
            <h5>Cliente</h5>
            <button class="btn btn-primary btn-sm"><i class="bx bx-plus"></i></button>
          </div>
        </div>
        <div class="col-12 mb-3">
          <div class="card shadow-sm p-3">
            <h5>2 items</h5>
            <!-- Listado de items seleccionados -->
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <img src="https://via.placeholder.com/50" alt="Producto 1" class="img-thumbnail me-2">
                  <div>
                    <h6 class="mb-0">Producto 1</h6>
                    <small class="text-muted">Descripción del producto</small>
                  </div>
                </div>
                <span>$10.00</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <img src="https://via.placeholder.com/50" alt="Producto 2" class="img-thumbnail me-2">
                  <div>
                    <h6 class="mb-0">Producto 2</h6>
                    <small class="text-muted">Descripción del producto</small>
                  </div>
                </div>
                <span>$20.00</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <img src="https://via.placeholder.com/50" alt="Producto 3" class="img-thumbnail me-2">
                  <div>
                    <h6 class="mb-0">Producto 3</h6>
                    <small class="text-muted">Descripción del producto</small>
                  </div>
                </div>
                <span>$30.00</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-12">
          <div class="card shadow-sm p-3">
            <h5>Observación</h5>
            <textarea class="form-control" placeholder="Digite la observación aquí"></textarea>
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="mostrarRecibo">
              <label class="form-check-label" for="mostrarRecibo">Mostrar en el recibo</label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 mb-3">
        <h5>Resumen del pedido</h5>
        <div class="d-flex justify-content-between">
          <span>Subtotal de productos</span>
          <span>$1,300.00</span>
        </div>
        <div class="d-flex justify-content-between">
          <span>Descuentos</span>
          <span>$0.00</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between">
          <strong>Total</strong>
          <strong>$1,300.00</strong>
        </div>
      </div>
      <div class="card shadow-sm p-3">
        <h5>Seleccione el método de pago</h5>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="cash" checked>
          <label class="form-check-label" for="cash">Efectivo - Vuelto: $700</label>
          <input type="text" class="form-control mt-2" placeholder="Valor recibido">
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="debit">
          <label class="form-check-label" for="debit">Débito</label>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="credit">
          <label class="form-check-label" for="credit">Crédito</label>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="other">
          <label class="form-check-label" for="other">Otros</label>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="paymentMethod" id="creditSale">
          <label class="form-check-label" for="creditSale">Venta a crédito</label>
        </div>
      </div>
      <div class="demo-inline-spacing d-flex justify-content-between">
        <button class="btn btn-light"><i class="bx bx-x"></i> Descartar venta</button>
        <button class="btn btn-secondary"><i class="bx bx-save"></i> Guardar pedido</button>
        <button class="btn btn-success"><i class="bx bx-check"></i> Finalizar venta</button>
      </div>
    </div>
  </div>
</div>


@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/jquery/jquery.min.js',
    'resources/assets/vendor/libs/popper/popper.min.js',
    'resources/assets/vendor/libs/bootstrap/bootstrap.min.js'
])
@endsection
