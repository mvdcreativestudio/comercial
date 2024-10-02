@extends('layouts/layoutMaster')

@section('title', 'Pasos de la fórmula')

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
    window.csrfToken = "{{ csrf_token() }}";
    var purchaseOrderItems = @json($formulaRawMaterials);
</script>
@vite(['resources/assets/js/app-formula-raw-materials-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Fórmula /</span> Pasos
</h4>

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

<div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
    <div class="row gx-4 gy-3">
  <!-- Cards Section -->
<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="d-flex justify-content-between align-items-start p-3">
        <div>
          <h6 class="mb-2">Pasos</h6>
          <h4 class="mb-2 total-steps">0</h4>
          <p class="mb-0 text-muted">Total</p>
        </div>
        <div class="avatar">
          <span class="avatar-initial rounded bg-label-secondary">
           <i class="bx bx-list-check bx-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card">
      <div class="d-flex justify-content-between align-items-start p-3">
        <div>
          <h6 class="mb-2">Materias primas diferentes</h6>
          <h4 class="mb-2 total-raw-material">0</h4>
          <p class="mb-0 text-muted">Total</p>
        </div>
        <div class="avatar">
          <span class="avatar-initial rounded bg-label-secondary">
            <i class="bx bx-cube bx-sm"></i>
          </span>
        </div>
      </div>
    </div>
  </div>

  
</div>

<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Pasos de la fórmula</h5>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#csvModal">Cargar CSV</button>
      </div>
    <div class="d-flex">
      <p class="text-muted small">
        <a href="#" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
      </p>
    </div>
  </div>
  <div class="collapse" id="columnSwitches">
    <div class="mt-0 d-flex flex-wrap">
      @foreach (['ID', 'Fórmula', 'Materia Prima', 'Cantidad', 'Aclaración', 'Paso', 'Acciones'] as $index => $label)
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="{{ $index }}" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">{{ $label }}</span>
          </label>
        </div>
      @endforeach
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <div class="dataTables_wrapper" style="padding-left: 1rem; padding-right: 1rem;">
      <table class="table datatables-formula-steps border-top">
          <thead>
            <tr>
              <th>ID</th>
              <th>Fórmula</th>
              <th>Materia Prima</th>
              <th>Cantidad</th>
              <th>Aclaración</th>
              <th>Paso</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <!-- Aquí se debería cargar el contenido de la tabla mediante DataTables -->
        </table>
    </div>
  </div>
</div>



<!-- Modal -->
<div class="modal fade" id="csvModal" tabindex="-1" aria-labelledby="csvModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="csvModalLabel">Cargar archivo CSV</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="csvUploadForm" enctype="multipart/form-data">
          <div class="d-flex justify-content-center align-items-center flex-column p-5 border border-2 border-dashed rounded" id="dropzone">
            <i class="bx bx-cloud-upload bx-lg mb-3"></i>
            <p class="mb-0">Arrastra y suelta el archivo aquí o</p>
            <button type="button" class="btn btn-outline-primary mt-2" id="uploadButton">Subir archivo</button>
            <input class="file-input" type="file" id="csvFile" name="csvFile" accept=".csv" style="display:none;">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="csvUploadForm" class="btn btn-primary">Enviar</button>
      </div>
    </div>
  </div>
</div>
@endsection
