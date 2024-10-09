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
  window.packagings = @json($packagings);
  window.csrfToken = "{{ csrf_token() }}";
</script>
@vite(['resources/assets/js/app-packagings-list.js'])
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
  <span class="text-muted fw-light">Producción /</span> Envasado
</h4>

<div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Total de envasados</h6>
              <h4 class="mb-2">{{ $packagings->count() }}</h4>
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
              <h6 class="mb-2">Envasados recientes</h6>
              <h4 class="mb-2">{{ $packagings->where('created_at', '>=', now()->subMonth())->count() }}</h4>
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
              <h6 class="mb-2">Envasados del día</h6>
              <h4 class="mb-2">{{ $packagings->where('created_at', '>=', now()->startOfDay())->count() }}</h4>
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

<div class="d-flex justify-content-end mb-4">
    <button type="button" id="openProductionModal" class="btn btn-primary" data-toggle="modal" data-target="#productionModal">
      Abrir Modal de Producción
    </button>
    <button class="btn btn-primary ms-2" id="loadPackagesButton">
       Stock Botellas
    </button>
</div>

<div class="container mt-4">
    <div class="row">
        @foreach($packagings as $packaging)
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm hover-shadow">
                    <div class="card-body">
                        <p class="card-text">
                            <strong>Cantidad envasada:</strong> {{ $packaging->quantity_packaged }}<br>
                            <strong>De la producción:</strong> {{ $packaging->bulk_production_id }}
                            <strong>En el envase:</strong> {{ $packaging->package_id }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal de Bootstrap -->
<div class="modal fade" id="productionModal" tabindex="-1" role="dialog" aria-labelledby="productionModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productionModalLabel">Iniciar Empaque</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="productionForm">
          <label for="bulk_production">Producciones a envasar:</label>
          <select id="bulk_production" name="bulk_production" class="form-control">
            <!-- Aquí se llenarán las opciones mediante JS -->
          </select>
          <label for="package_id">Envase:</label>
          <select id="package_id" name="package_id" class="form-control">
            <!-- Aquí se llenarán los packages mediante JS -->
          </select>
          <label for="quantity_packaged">Cantidad a envasar:</label>
          <input type="number" id="quantity_packaged" name="quantity_packaged" class="form-control" required />
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary" id="submitProductionForm">Iniciar Empaque</button>
      </div>
    </div>
  </div>
</div>




@endsection