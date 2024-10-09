@extends('layouts/layoutMaster')

@section('title', 'Elaboraciones')

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
@endsection

@section('page-script')
<script type="text/javascript">
  window.packages = @json($packages);
  window.csrfToken = "{{ csrf_token() }}";
</script>
@vite(['resources/assets/js/app-packages-list.js'])
@endsection

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
  {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
  {{ $error }}
</div>
@endforeach
@endif

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Stock /</span> Envasado
</h4>

<div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Total de envasados</h6>
              <h4 class="mb-2">{{ $packages->count() }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total</span></p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-list-ul bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-4">
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Stock agregado recientemente</h6>
              <h4 class="mb-2">{{ $packages->where('created_at', '>=', now()->subMonth())->count() }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Último mes</span></p>
            </div>
            <div class="avatar me-lg-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-calendar bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
        <div class="col-sm-12 col-lg-4">
          <div class="d-flex justify-content-between align-items-start pb-3 pb-sm-0 card-widget-3">
            <div>
              <h6 class="mb-2">Stock agregado hoy</h6>
              <h4 class="mb-2">{{ $packages->where('created_at', '>=', now()->startOfDay())->count() }}</h4>
              <p class="mb-0 text-muted">Hoy</p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-time-five bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Botón para abrir el offcanvas alineado a la derecha -->
<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasPackage" aria-controls="offcanvasPackage">
      Agregar Package
    </button>
</div>

<div class="container mt-4">
    <div class="row">
        @foreach($packages as $package)
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm hover-shadow">
                    <div class="card-body">
                        <h5 class="card-title">{{ $package->name }}</h5>
                        <p class="card-text">
                            <strong>Tamaño:</strong> {{ $package->size }} {{ $package->unit_of_measure }}<br>
                            <strong>Stock:</strong> {{ $package->stock }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasPackage" aria-labelledby="offcanvasPackageLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasPackageLabel">Agregar Package</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="addPackageForm">
      <div class="mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <div class="mb-3">
        <label for="is_sellable" class="form-label">Es vendible</label>
        <select class="form-control" id="is_sellable" name="is_sellable" required>
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="cost" class="form-label">Costo</label>
        <input type="number" step="0.01" class="form-control" id="cost" name="cost" required>
      </div>
      <div class="mb-3">
        <label for="price" class="form-label">Precio</label>
        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
      </div>
      <div class="mb-3">
        <label for="unit_of_measure" class="form-label">Unidad de Medida</label>
        <select class="form-control" id="unit_of_measure" name="unit_of_measure" required>
          <option value="L">Litros</option>
          <option value="ml">Mililitros</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="size" class="form-label">Tamaño</label>
        <input type="number" step="0.01" class="form-control" id="size" name="size" required>
      </div>
      <div class="mb-3">
        <label for="stock" class="form-label">Stock</label>
        <input type="number" class="form-control" id="stock" name="stock" required>
      </div>
      <button type="submit" class="btn btn-success">Guardar</button>
    </form>
  </div>
</div>

@endsection

