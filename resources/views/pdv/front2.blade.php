@extends('layouts.blankLayout')

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
      <h2 class="mb-0"><button class="btn m-0 p-0"><i class="bx bx-chevron-left fs-2"></i></button> Vender <span class="text-secondary fs-4">$1,300.00</span></h2>
      <div class="d-flex align-items-center">
        <button class="btn btn-light me-2"><i class="bx bx-help-circle"></i> Ayuda</button>
        <div class="d-flex align-items-center">
          <span class="me-2">Martín Santamaría</span>
          <img src="https://via.placeholder.com/40" class="rounded-circle" alt="User">
        </div>
      </div>
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
          <label class="form-check-label" for="cash">Efectivo</label>
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
      <div class="d-flex justify-content-between mt-3">
        <button class="btn btn-light col-4"><i class="bx bx-x"></i> Descartar venta</button>
        <button class="btn btn-secondary col-4"><i class="bx bx-save"></i> Guardar pedido</button>
        <button class="btn btn-success col-4"><i class="bx bx-check"></i> Finalizar venta</button>
      </div>
    </div>
  </div>
</div>

<style>
  .card {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  padding: 20px;
}

.btn-primary {
  background-color: #007bff;
  border-color: #007bff;
}

.btn-light {
  background-color: #f8f9fa;
  border-color: #f8f9fa;
}

.btn-secondary {
  background-color: #6c757d;
  border-color: #6c757d;
}

.btn-success {
  background-color: #28a745;
  border-color: #28a745;
}

.form-check-label {
  margin-left: 10px;
}

.form-control {
  border-radius: 5px;
  border: 1px solid #ced4da;
  margin-top: 10px;
}

textarea.form-control {
  height: 100px;
}

.form-check-input:checked {
  background-color: #007bff;
  border-color: #007bff;
}

.text-secondary {
  color: #6c757d !important;
}

.rounded-circle {
  border-radius: 50%;
}

.badge.bg-primary {
  background-color: #007bff;
}

.d-flex {
  display: flex;
}

.justify-content-between {
  justify-content: space-between;
}

.align-items-center {
  align-items: center;
}

</style>
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/jquery/jquery.min.js',
    'resources/assets/vendor/libs/popper/popper.min.js',
    'resources/assets/vendor/libs/bootstrap/bootstrap.min.js'
])
@endsection
