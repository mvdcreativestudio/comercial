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

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <h2 class="mb-4">Punto de Venta</h2>
    </div>

    <div class="col-md-8">
      <div class="row d-flex search-bar-section align-items-center p-3 mb-4">
        {{-- Buscador de productos --}}
        <div class="col-md-4">
          <div class="input-group">
            <input class="form-control" type="search" placeholder="Nombre o código" id="html5-search-input" />
            <button class="btn btn-primary"><i class="bx bx-search-alt"></i></button>
          </div>
        </div>
        {{-- Fin buscador de productos --}}
        <div class="col-md-4">
          <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Categorías
            </button>
            <div class="dropdown-menu dropdown-menu-end w-px-300">
              <form class="p-4" onsubmit="return false">
                {{-- Buscador de categorías --}}
                <div class="mb-3">
                  <label for="categorySearchInput" class="form-label"><h5>Filtrar por categoría</h5></label>
                  <input type="text" class="form-control" id="categorySearchInput" placeholder="Buscar categoría">
                </div>
                {{-- Opciones de categorías --}}
                <div class="mb-3">
                  <div class="form-check form-check-primary mt-1">
                    <input class="form-check-input" type="checkbox" value="" id="customCheckPrimary1" checked />
                    <label class="form-check-label" for="customCheckPrimary1">Helados</label>
                  </div>
                  <div class="form-check form-check-primary mt-1">
                    <input class="form-check-input" type="checkbox" value="" id="customCheckPrimary2"/>
                    <label class="form-check-label" for="customCheckPrimary2">Paletas</label>
                  </div>
                  <div class="form-check form-check-primary mt-1">
                    <input class="form-check-input" type="checkbox" value="" id="customCheckPrimary3"/>
                    <label class="form-check-label" for="customCheckPrimary3">Tortas</label>
                  </div>
                  <div class="form-check form-check-primary mt-1">
                    <input class="form-check-input" type="checkbox" value="" id="customCheckPrimary4"/>
                    <label class="form-check-label" for="customCheckPrimary4">Línea Zero</label>
                  </div>
                  <div class="form-check form-check-primary mt-1">
                    <input class="form-check-input" type="checkbox" value="" id="customCheckPrimary5"/>
                    <label class="form-check-label" for="customCheckPrimary5">Adicionales</label>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="col-md-4 d-flex justify-content-end">
          <button class="btn btn-light" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="left" data-bs-html="true" title="<span>Habilitar lector de código de barra</span>">
            <i class="bx bx-barcode-reader fs-2"></i>
          </button>
          <button class="btn btn-light" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="left" data-bs-html="true" title="<span>Vender producto no registrado</span>">
            <i class="bx bx-no-entry fs-2"></i>
          </button>
          <button class="btn btn-light" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="left" data-bs-html="true" title="<span>Ver productos en lista</span>">
            <i class="bx bx-list-ul fs-2"></i>
          </button>
        </div>
      </div>

      <div class="row d-flex flex-wrap">
        @foreach ($products as $product)
        <div class="col-md-3 mb-2 card-product-pos d-flex align-items-stretch">
          <div class="card-product-pos w-100 mb-3 position-relative">
            <img src="{{ asset($product->image) }}" class="card-img-top-product-pos" alt="{{ $product->name }}">
            <div class="card-img-overlay-product-pos d-flex flex-column justify-content-end">
              <h5 class="card-title-product-pos">{{ $product->name }}</h5>
              <p class="card-text-product-pos">${{ $product->old_price }}</p>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    <div class="col-md-4">
      <div id="cart" class="card shadow-sm p-3">
        <div class="text-end">
          <button class="btn btn-primary btn-sm">Seleccionar cliente</button>
        </div>
        <table class="table table-hover">
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
            <tr>
              <td>
                <img src="{{ asset($product->image) }}" alt="Producto 1" class="img-thumbnail me-2">
                Paleta Chanchito
              </td>
              <td>
                <nav aria-label="Page navigation">
                  <ul class="pagination">
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0);"><i class="tf-icon bx bx-minus"></i></a>
                    </li>
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0);">1</a>
                    </li>
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0);"><i class="tf-icon bx bx-plus"></i></a>
                    </li>
                  </ul>
                </nav>
              </td>
              <td>$200</td>
              <td>$200</td>
              <td>
                <button class="btn btn-sm"><i class="fa fa-x"></i></button>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
              <td>$200</td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3" class="text-right"><strong>Envío:</strong></td>
              <td>$60</td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3" class="text-right"><strong>Descuento:</strong></td>
              <td>$0</td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3" class="text-right"><strong>Total:</strong></td>
              <td><b>$260</b></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
        <div class="d-flex mt-3">
          <div class="col-1 icono-trash">
            <button class="btn w-100 h-100" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="left" data-bs-html="true" title="<span>Limpiar carrito</span>">
              <i class="fa fa-trash"></i>
            </button>
          </div>
          <div class="col-11">
            <button class="btn btn-primary btn-block mx-1 col-12 d-flex justify-content-between align-items-center" onclick="window.location.href='{{ route('pdv.front2') }}'">
              <span class="mx-auto text-white">Ir al pago</span>
              <span class="tf-icons bx bx-chevron-right fs-2"></span>
            </button>
          </div>

        </div>
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
